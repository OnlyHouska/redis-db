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

const Task: React.FC<TaskProps> = ({ task, onToggleComplete, onDelete }) => {
    return (
        <div>
            <div>
                <h3>{task.title}</h3>
                <span>{task.category}</span>
            </div>

            <p>{task.description}</p>

            <div>
                <span>{new Date(task.created_at).toLocaleDateString('en-US')}</span>

                <div>
                    <button onClick={() => onToggleComplete(task.id)}>
                        {task.completed ? '↶ Undo' : '✓ Complete'}
                    </button>

                    <button onClick={() => onDelete(task.id)}>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    );
};

export default Task;
