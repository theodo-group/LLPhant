# Quickstart for the AutoPHP using LLPhant

Quick hacky demo to use AutoPHP through a web interface.
You will need to have an OpenAI API key to use this and a SERP API key if you want to be able to search the web.


## Start the [Laravel](https://laravel.com) backend

### Set your OpenAI API key
- go to the `backend` folder
- create a copy of the `.env.example` file and rename it to `.env`
- set the value of OPENAI_API_KEY to your OpenAI API key
- or you can export the OPENAI_API_KEY environment variable in your terminal

### Start the backend serverS
- install [composer](https://getcomposer.org/download/)
- (still in the backend folder) run `composer install` to install dependencies
- run `php artisan key:generate` to generate an application key
- run twice `php artisan serve` to start the backend serverS

You'll need to start a second server that will be use to send the last information to the frontend 
while the other is used to process the AutoPHP request.
They should listen to 8000 and 8001 ports.

## Start the [Next.js](https://nextjs.org) frontend

- go to the `frontend` folder
- run `npm install` to install dependencies
- run `npm run dev` to start the frontend server
- open [http://localhost:3000](http://localhost:3000) in your browser to see the chatbot 🎉
- all the custom code is in the `frontend/src/app/page.tsx` file. 
