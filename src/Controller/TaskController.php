<?php
/**
 * TaskController.php
 *
 * @project take-home
 *
 */

namespace App\Controller;

use App\Entity\Task;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use App\Utility\TaskValidator;
use Psr\Cache;


/**
 * Class TaskController
 */
class TaskController extends Controller
{
    const TASK_CACHE_KEY        = 'tasks_key';
    const TASK_CACHE_KEY_PREFIX = 'task_';
    const TASK_PROCESSOR_ID_KEY = 'processor_id';
    const TASK_COMMAND_KEY      = 'command';
    const TASK_STARTED_KEY      = 'task_started';
    const TASK_COMPLETED_KEY    = 'task_completed';
    
    private $cacheDriver;
    
    /**
     * @Route("/tasks/task/{id}", methods={"GET"})
     *
     */
    public function getTaskByIdAction($id)
    {
        $this->initCache();
        
        $validator = new TaskValidator();
        
        if (!$validator->isValidId($id)) {
            $result = ["message"=>"Invalid ID"];
    
            return new Response(json_encode($result), 400);
        }
    
        $cacheKey = self::TASK_CACHE_KEY_PREFIX . $id;
        
        $cachedTask = $this->cacheDriver->getItem($cacheKey);
        
        if (!$cachedTask->isHit()) {
            $em = $this->getDoctrine()->getManager();
            $task = $em->getRepository(Task::class)->find($id);
            
            if ($task instanceof Task) {
                $cachedTask->set($task);
                $cachedTask->expiresAfter(3600); //expires in 1 hour
                $this->cacheDriver->save($cachedTask);
            }
        } else {
            $task = $cachedTask->get();
        }

        if ($task instanceof Task) {
            $result = [
                "task" => $task->toArray()
            ];
        } else {
            $result = [
                "message" => "There is no task with that ID in our system."
            ];
    
        }
        
        return new Response(json_encode($result));
    }
    
    /**
     * @Route("/tasks/task", methods={"POST"})
     */
    public function createTaskAction(Request $request)
    {
        $em        = $this->getDoctrine()->getManager();
        $data      = json_decode($request->getContent(), true);
        $validator = new TaskValidator();
        
        if (!$validator->isValidId($data['submitter_id']) || !$validator->isValidCommand($data['command'])) {
            $result = ["message"=>"Invalid Payload"];
            return new Response(json_encode($result), 400);
        }
        
        $task = new Task($data['submitter_id'], $data['command']);
        $task->setTaskCreated(time());
        
        $em->persist($task);
        $em->flush();
        
        $result = [
            "message"=>"success",
            "task_id" => $task->getId()
        ];
        
        $this->deleteCacheItem(self::TASK_CACHE_KEY);
        
        return new Response(json_encode($result));
    }
    
    
    /**
     ** @Route("/tasks/task", methods={"GET"})
     */
    public function getNextTaskAction() {
        $this->initCache();
        
        $result = ['message' => "There are no tasks"];
        
        $em   = $this->getDoctrine()->getManager();
        
        $cachedTasks = $this->cacheDriver->getItem(self::TASK_CACHE_KEY);
        
        if (!$cachedTasks->isHit()) {
            $query = $em->createQuery(
                'SELECT t
            FROM App\Entity\Task t
            WHERE t.task_started IS NULL
            AND t.task_completed IS NULL
            ORDER BY t.id ASC            
            '
            )->setMaxResults(1);
    
            $tasks = $query->getResult();
    
            $cachedTasks->set($tasks);
            $cachedTasks->expiresAfter(3600); //expires in 1 hour
            $this->cacheDriver->save($cachedTasks);
    
        } else {
            $tasks = $cachedTasks->get();
        }
        
        if (count($tasks)) {
            $task = current($tasks);
    
            $result = [
                "task" => $task->toArray()
            ];
        }
    
        return new Response(json_encode($result));
    
    }
    
    
    /**
     * @Route("/tasks/task/{id}", methods={"PATCH"})
     */
    public function updateTaskAction(Request $request, $id) {
        $validator = new TaskValidator();
    
        if (!$validator->isValidId($id)) {
            $result = ["message" => "Invalid ID"];
        
            return new Response(json_encode($result), 400);
        }
    
        $em   = $this->getDoctrine()->getManager();
        $task = $em->getRepository(Task::class)->find($id);
    
        $whiteListedProperties = [
            self::TASK_PROCESSOR_ID_KEY => true,
            self::TASK_COMMAND_KEY      => true,
            self::TASK_STARTED_KEY      => true,
            self::TASK_COMPLETED_KEY    => true,
        ];
    
    
        $data = json_decode($request->getContent(), true);
        
        foreach ($data as $key=>$value) {
            if (!isset($whiteListedProperties[$key])) {
                continue;
            }
        
            switch ($key) {
                case self::TASK_PROCESSOR_ID_KEY:
                    if (!$validator->isValidId($value)) {
                        $result = ["message" => "Invalid processor ID"];
        
                        return new Response(json_encode($result), 400);
                    }
                    
                    $task->setProcessorId($value);
                    break;
                case self::TASK_STARTED_KEY:
                case self::TASK_COMPLETED_KEY:
                    if (!$validator->isValidTimestamp($value)) {
                        $result = ["message" => "Invalid timestamp"];
    
                        return new Response(json_encode($result), 400);
                    }
                    
                    if ($key == self::TASK_STARTED_KEY) {
                        $task->setTaskStarted($value);
                    } else {
                        $task->setTaskCompleted($value);
                    }
                    break;
                    
                case self::TASK_COMMAND_KEY:
                    if (!$validator->isValidCommand($value)) {
                        $result = ["message" => "Invalid command value"];
    
                        return new Response(json_encode($result), 400);
                    }
                    
                    $task->setCommand($value);
                    break;
            }
            
        }
        
        $em->persist($task);
        $em->flush();
    
        $result = [
            "message"=>"success",
            "task" => $task->toArray()
        ];
        
        $cacheKey = self::TASK_CACHE_KEY_PREFIX . $id;
        $this->deleteCacheItem($cacheKey);
        $this->deleteCacheItem(self::TASK_CACHE_KEY);
        
        return new Response(json_encode($result));
    }

    /**
     * @Route("/tasks/task/{id}", methods={"DELETE"})
     */
    public function deleteTaskAction($id) {
        $message = "Task was not removed";
        
        $validator = new TaskValidator();
    
        if (!$validator->isValidId($id)) {
            $result = ["message" => "Invalid ID"];
        
            return new Response(json_encode($result), 400);
        }
    
        $em   = $this->getDoctrine()->getManager();
        $task = $em->getRepository(Task::class)->find($id);
        
        if ($task instanceof Task) {
            $em->remove($task);
            $em->flush();
            $message = "Task was successfully removed";
        }
    
        $result = [
            'message' => $message
        ];
    
        $cacheKey = self::TASK_CACHE_KEY_PREFIX . $id;
        $this->deleteCacheItem($cacheKey);
    
        return new Response(json_encode($result));
    }
    
    /**
     * Helper to instantiate cache
     */
    private function initCache() {
    
        /** @var  cacheDriver  CacheItemPoolInterface*/
        $this->cacheDriver = $this->get('cache.app');
    }
    
    /**
     * Helper to delete item from cache
     *
     * @param $key
     */
    private function deleteCacheItem($key) {
        $this->initCache();
    
        $this->cacheDriver->deleteItem($key);
    }
}