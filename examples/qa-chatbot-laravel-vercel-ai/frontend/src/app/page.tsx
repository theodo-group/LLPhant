'use client'

import { useChat } from 'ai/react'

export default function Chat() {
    const { messages, input, handleInputChange, handleSubmit } = useChat({api: 'http://127.0.0.1:8000/api/chat'})

    return (
        <main className="min-h-screen bg-gray-800 flex flex-col items-center justify-between px-4 sm:px-8 md:px-24">
            <div className="mx-auto w-full max-w-2xl py-12 sm:py-24 flex flex-col stretch">
                <h1 className="text-3xl sm:text-4xl text-white font-bold mb-4 sm:mb-8">Welcome on The Star by H.G. Wells (1897) AI. Ask me anything!</h1>
                {messages.map(m => (
                    <div key={m.id} className={`p-2 sm:p-4 my-2 sm:my-4 rounded-lg ${m.role === 'user' ? 'bg-blue-500 text-white' : 'bg-gray-700 text-gray-300'}`}>
                        <strong className="font-bold">{m.role === 'user' ? 'You: ' : 'AI: '}</strong>
                        <p className="my-1">{m.content}</p>
                    </div>
                ))}

                <form onSubmit={handleSubmit} className="fixed w-11/12 max-w-md bottom-0 mb-8 shadow-xl p-2">
                    <input
                        className="bg-gray-900 text-white border border-gray-700 rounded-lg w-full max-w-md shadow-xl p-2"
                        value={input}
                        placeholder="Ask questions about The Star"
                        onChange={handleInputChange}
                    />
                </form>
            </div>
        </main>
    )
}
