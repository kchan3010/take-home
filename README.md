This app was built with Symfony 3.4 with MAMP on my local machine.

The app assumes a host of localhose:3306 for the MySQL db.

The base endpoint URLs is "http://localhost:8888/take-home/public".
The routes are set within the controller using annoations. So it should
be clear what the endpoints will look like.

POST payload should look like this:
{
	"submitter_id": 3408,
	"command": "wait for it"
}

PATCH payload should look like this where each key is optional:
{
	"processor_id": 9997,
	"task_started": 1577924745,
	"task_completed": 1580084745,
	"command": "new command"
}

I have add some basic unit testing for the Validator class 
that I'm using to help with some basic input validation.