import React from 'react';

export interface TaskData {
    id: number;
    title: string;
    description: string;
    category: string;
    completed: boolean;
    created_at: string;
    due_date?: string;
}

interface TaskProps {
    task: TaskData;
    onToggleComplete: (id: number) => void;
    onDelete: (id: number) => void;
}

/**
 * Task card component
 *
 * Displays task information with complete/undo and delete actions.
 * Styling changes based on completion status.
 */
const Task: React.FC<TaskProps> = ({ task, onToggleComplete, onDelete }) => {
    return (
        <div className="border-1 p-4 m-2 rounded-md shadow-md w-[400px]">
            <div className="flex justify-between items-center mb-2">
                <h3 className="font-bold">{task.title}</h3>
                <h3 className="text-gray-500">{task.category}</h3>
            </div>

            <p className="italic">{task.description}</p>

            <div>
                <p>Due date: <span className="font-bold">{new Date(task.created_at).toLocaleDateString('en-US')}</span></p>

                <div className="mt-4 flex">
                    {/* Toggle completion status - button style changes based on current state */}
                    <button onClick={() => onToggleComplete(task.id)}
                            className={`border-1 px-3 py-1 rounded-md hover:border-black hover:text-black transition-all cursor-pointer mr-2 ${task.completed ? 'border-gray-500 text-gray-500 hover:bg-gray-500' : 'border-green-500 text-green-500 hover:bg-green-500'}`}>
                        {task.completed ? '↶ Undo' : '✓ Complete'}
                    </button>

                    <button onClick={() => onDelete(task.id)}
                            className="border-1 border-red-500 px-3 py-1 rounded-md hover:border-black hover:text-black hover:bg-red-500 transition-all cursor-pointer">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    );
};

export default Task;
