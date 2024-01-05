# Quickstart for the QA Chatbot using LLPhant

## Start the [Laravel](https://laravel.com) backend

### Set your OpenAI API key
- go to the `backend` folder
- create a copy of the `.env.example` file and rename it to `.env`
- set the value of OPENAI_API_KEY to your OpenAI API key

### Start the backend server
- install [composer](https://getcomposer.org/download/)
- (still in the backend folder) run `composer install` to install dependencies
- run `php artisan key:generate` to generate an application key
- run `php artisan serve` to start the backend server

## Start the [Next.js](https://nextjs.org) frontend

- go to the `frontend` folder
- run `npm install` to install dependencies
- run `npm run dev` to start the frontend server
- open [http://localhost:3000](http://localhost:3000) in your browser to see the chatbot 🎉
