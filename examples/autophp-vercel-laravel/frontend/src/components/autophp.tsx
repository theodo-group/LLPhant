/**
 * This code was generated by v0 by Vercel.
 * @see https://v0.dev/t/mVdoK7NzaxO
 */
import Link from "next/link"
import { Input } from "@/components/ui/input"
import { TableHead, TableRow, TableHeader, TableCell, TableBody, Table } from "@/components/ui/table"
import { Badge } from "@/components/ui/badge"
import { ScrollArea } from "@/components/ui/scroll-area"

export function autophp() {
  return (
    <div key="1" className="grid min-h-screen w-full">
      <div className="flex flex-col">
        <header className="flex h-14 items-center gap-4 border-b bg-gray-100/40 px-6">
          <Link className="lg:hidden" href="#">
            <BotIcon className="h-6 w-6" />
            <span className="sr-only">Home</span>
          </Link>
          <div className="w-full flex-1">
            <form>
              <div className="relative">
                <SearchIcon className="absolute left-2.5 top-2.5 h-4 w-4 text-gray-500" />
                <Input
                  className="w-full bg-white shadow-none appearance-none pl-8 md:w-2/3 lg:w-1/3"
                  placeholder="Search tasks..."
                  type="search"
                />
              </div>
            </form>
          </div>
        </header>
        <main className="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-6">
          <div className="flex items-center">
            <h1 className="font-semibold text-lg md:text-2xl">Tasks</h1>
          </div>
          <div className="border shadow-sm rounded-lg">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead className="w-[100px]">Task Name</TableHead>
                  <TableHead>Description</TableHead>
                  <TableHead>Result from the Task</TableHead>
                  <TableHead>Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow>
                  <TableCell className="font-medium">Task 1</TableCell>
                  <TableCell>Task Description 1</TableCell>
                  <TableCell>Result 1</TableCell>
                  <TableCell>
                    <Badge className="bg-green-500">Done</Badge>
                  </TableCell>
                </TableRow>
                <TableRow>
                  <TableCell className="font-medium">Task 2</TableCell>
                  <TableCell>Task Description 2</TableCell>
                  <TableCell>Result 2</TableCell>
                  <TableCell>
                    <Badge className="bg-yellow-500">Doing</Badge>
                  </TableCell>
                </TableRow>
                <TableRow>
                  <TableCell className="font-medium">Task 3</TableCell>
                  <TableCell>Task Description 3</TableCell>
                  <TableCell>Result 3</TableCell>
                  <TableCell>
                    <Badge className="bg-red-500">Failed</Badge>
                  </TableCell>
                </TableRow>
                <TableRow>
                  <TableCell className="font-medium">Task 4</TableCell>
                  <TableCell>Task Description 4</TableCell>
                  <TableCell>Result 4</TableCell>
                  <TableCell>
                    <Badge className="bg-blue-500">Todo</Badge>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
          <div className="mt-4">
            <h2 className="font-semibold text-lg md:text-2xl">Current Logs</h2>
            <div className="border shadow-sm rounded-lg p-4 mt-2">
              <ScrollArea className="h-64">
                <p className="text-sm">Log 1: Task 1 started...</p>
                <p className="text-sm">Log 2: Task 1 completed successfully...</p>
                <p className="text-sm">Log 3: Task 2 started...</p>
              </ScrollArea>
            </div>
          </div>
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
