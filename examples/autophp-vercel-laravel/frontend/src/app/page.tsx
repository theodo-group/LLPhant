'use client'
/**
 * v0 by Vercel.
 * @see https://v0.dev/t/mVdoK7NzaxO
 */
import Link from "next/link"
import { Input } from "@/components/ui/input"
import { TableHead, TableRow, TableHeader, TableCell, TableBody, Table } from "@/components/ui/table"
import { Badge } from "@/components/ui/badge"
import { ScrollArea } from "@/components/ui/scroll-area"
import React, { useState } from 'react';

let currentRunId = null;
let pollInterval = null;

export default function Component() {
    const [input, setInput] = useState('');
    const [result, setResult] = useState('');
    const [tasks, setTasks] = useState([]);
    const [messages, setMessages] = useState([]);
    const handleInputChange = (event) => {
        setInput(event.target.value); // Update the state with the input value
    };

    const handleSubmit = (e) => {
        // We create a new autophp run that will be identified by the currentRunId
        // obviously this is not a good way to do it in production, but it's just a demo
        currentRunId = Math.random().toString(32).slice(2);
        e.preventDefault()
        console.log('input', input)
        fetch('http://localhost:8000/api/chat', {
            method: "POST",
            body: JSON.stringify({objective: input, id: currentRunId })
        }).then((response) => {
            console.log('response', response)
            if (!(response.status === 200)) {
                throw new Error('Something went wrong on api server!');
            } else {
                console.log('AutoPHP started');
                pollInterval = setInterval(getLastInfo, 1000);
            }

        }).catch((error) => {
            console.error('Error:', error);
        });
    }

    const getLastInfo = () => {
        if (!currentRunId) {
            return;
        }
        const response = fetch('http://127.0.0.1:8001/api/outputs?id=' + currentRunId, {
            method: "GET",
        }).then((response) => {
            return response.json(); // Parse the response as JSON
        }).then((data) => {
            console.log('LastInfo:', data);
            setTasks(data.tasks);
            setMessages(data.messages);
            if (data.result) {
                setResult(data.result);
                clearInterval(pollInterval);
                pollInterval= null;
            }
        }).catch((error) => {
            console.error('Error:', error);
            clearInterval(pollInterval);
            pollInterval= null;
        });
    }

    return (
        <div key="1" className="grid min-h-screen w-full">
            <div className="flex flex-col">
                <header className="flex h-14 items-center gap-4 border-b bg-gray-100/40 px-6">
                    <Link className="lg:hidden" href="#">
                        <BotIcon className="h-6 w-6" />
                        <span className="sr-only">Home</span>
                    </Link>
                    <div className="w-full flex-1">
                        <form onSubmit={handleSubmit}>
                            <div className="relative">
                                <SearchIcon className="absolute left-2.5 top-2.5 h-4 w-4 text-gray-500"/>
                                    <Input
                                        className="w-full bg-white shadow-none appearance-none pl-8 md:w-2/3 lg:w-1/3"
                                        placeholder="Ask AutoPHP..."
                                        value={input}
                                        type={'text'}
                                        onChange={handleInputChange}
                                    />
                            </div>
                        </form>
                    </div>
                </header>
                <main className="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-6">
                    <div className="flex items-center">
                        <h1 className="font-semibold text-lg md:text-2xl">AutoPHP</h1>
                    </div>
                    <div className="border shadow-sm rounded-lg">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="w-[200px]">Task Name</TableHead>
                                    <TableHead>Description</TableHead>
                                    <TableHead>Result from the Task</TableHead>
                                    <TableHead>Status</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {tasks.map((t, index) => (
                                    <TableRow key={index}>
                                        <TableCell className="font-medium">{t.name}</TableCell>
                                        <TableCell>{t.description}</TableCell>
                                        <TableCell>{t.result}</TableCell>
                                        <TableCell>
                                            {t.result ? (t.wasSuccessful ?
                                                    <Badge className="bg-green-500">Done</Badge> :
                                                    <Badge className="bg-red-500">Failed</Badge>) :
                                                <Badge className="bg-blue-500">Todo</Badge>}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                    <div className="mt-4">
                        <h2 className="font-semibold text-lg md:text-2xl">Current Logs</h2>
                        <div className="border shadow-sm rounded-lg p-4 mt-2">
                            <ScrollArea className="h-96">
                                {messages.map((m, index) => (
                                    <div key={index}
                                         className={`p-2 sm:p-4 my-2 sm:my-4 rounded-lg bg-gray-700 text-gray-300`}>
                                        <p className="my-1">{m.content}</p>
                                    </div>
                                ))}
                            </ScrollArea>
                        </div>
                    </div>
                    {result ? (
                    <div className="mt-4">
                        <h2 className="font-semibold text-lg md:text-2xl">Result</h2>
                        <div className="border shadow-sm rounded-lg p-4 mt-2">
                            <p className="text-lg text-center">{result}</p>
                        </div>
                    </div>
                    ) : null}
                </main>
            </div>
        </div>
    )
}

function BotIcon(props) {
    return (
        <svg
            {...props}
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
        >
            <path d="M12 8V4H8" />
            <rect width="16" height="12" x="4" y="8" rx="2" />
            <path d="M2 14h2" />
            <path d="M20 14h2" />
            <path d="M15 13v2" />
            <path d="M9 13v2" />
        </svg>
    )
}


function SearchIcon(props) {
    return (
        <svg
            {...props}
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
        >
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.3-4.3" />
        </svg>
    )
}
