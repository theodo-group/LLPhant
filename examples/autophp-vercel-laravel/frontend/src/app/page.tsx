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
import { Button } from "@/components/ui/button"
import React, { useState } from 'react';

type StreamDataHandler = (chunk: string) => void;
type ErrorHandler = (error: any) => void;

let currentRunId = null;

async function handleStreamResponse(url: string, method: 'GET' | 'POST', onData: StreamDataHandler, onError: ErrorHandler, body?: any) {
    try {
        const response = await fetch(url, {
            method,
            body: method === 'POST' ? JSON.stringify(body) : undefined
        });

        if (!response.body) {
            throw new Error('ReadableStream not available in the response');
        }
        const reader = response.body.getReader();
        while (true) {
            const { done, value } = await reader.read();
            if (done) {
                break;
            }
            // Assuming the server sends UTF-8 encoded text data
            const chunk = new TextDecoder().decode(value);
            onData(chunk);
        }
    } catch (error) {
        onError(error);
    }
}

export default function Component() {
    const [input, setInput] = useState('');
    const [result, setResult] = useState('');
    const [tasks, setTasks] = useState([]);
    const [messages, setMessages] = useState([]);
    const handleInputChange = (event) => {
        setInput(event.target.value); // Update the state with the input value
    };


const handleSubmit = (e) => {
    currentRunId = null;
    console.log(input)
    e.preventDefault()
    console.log('submit')

    handleStreamResponse(
        'http://localhost:8000/api/chat',
        'POST',

        (chunk) => {
            console.log('Received chunk:', chunk);
            if (!currentRunId) {
                const jsonChunk = JSON.parse(chunk);
                currentRunId = jsonChunk.id;
                console.log('yeah'+currentRunId)
            }

            if (!currentRunId) {
                return;
            }

            // Handle the result
            try {
                const jsonChunk = JSON.parse(chunk);
                if (jsonChunk && jsonChunk.result) {
                    setResult(jsonChunk.result);
                    return;
                }
            } catch {}
            const response = fetch('http://127.0.0.1:8001/api/outputs?id='+ currentRunId, {
                method: "GET",
            }).then((response) => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json(); // Parse the response as JSON
            }).then((data) => {
                console.log(data); // Handle the JSON data
                if (data.result) {
                    setResult(data.result);
                }
                setTasks(data.tasks);
                setMessages(data.messages)
            })
        },
        (error) => {
            console.error('Stream error:', error);
        },
        { objective: input },
    ).then(() => console.log('Stream then'));
}


    // const { messages, input, handleInputChange, handleSubmit } = useChat({api: 'http://127.0.0.1:8000/api/chat'})

    // console.log(messages)

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
