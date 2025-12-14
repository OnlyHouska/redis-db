import React, { useState, useEffect } from 'react';
import axios from 'axios';
import Task, { TaskData } from './Task';
import CreateTaskModal, { NewTaskData } from './CreateTaskModal';

const TaskList: React.FC = () => {
    const [tasks, setTasks] = useState<TaskData[]>([]);
    const [filter, setFilter] = useState<string>('all');
    const [isModalOpen, setIsModalOpen] = useState(false);

    useEffect(() => {
        fetchTasks();
    }, []);

    const fetchTasks = async () => {
        try {
            const response = await axios.get<TaskData[]>('/api/tasks');
            setTasks(response.data);
        } catch (error) {
            console.error('Error loading tasks:', error);
        }
    };

    const handleCreateTask = async (taskData: NewTaskData) => {
        await axios.post(`/api/tasks/create`, taskData);
        fetchTasks();
    };

    const handleToggleComplete = async (id: number) => {
        try {
            await axios.put(`/api/tasks/${id}/toggle`);
            fetchTasks();
        } catch (error) {
            console.error('Error updating task:', error);
        }
    };

    const handleDelete = async (id: number) => {
        try {
            await axios.delete(`/api/tasks/${id}/delete`);
            setTasks(tasks.filter(task => task.id !== id));
        } catch (error) {
            console.error('Error deleting task:', error);
        }
    };

    const filteredTasks = tasks.filter(task => {
        if (filter === 'all') return true;
        if (filter === 'completed') return task.completed;
        if (filter === 'pending') return !task.completed;
        return task.category === filter;
    });

    return (
        <div className="w-[fit-content]">
            <div className="flex flex-row gap-5">
                <div className="border-2 border-black px-10 py-2 m-5 rounded-md w-[fit-content] flex gap-5">
                    <button className={filter=="all"?'underline cursor-pointer':'cursor-pointer'} onClick={() => setFilter('all')}>All</button>
                    <button className={filter=="pending"?'underline cursor-pointer':'cursor-pointer'} onClick={() => setFilter('pending')}>Active</button>
                    <button className={filter=="completed"?'underline cursor-pointer':'cursor-pointer'} onClick={() => setFilter('completed')}>Completed</button>
                </div>

                <div className="border-2 border-black px-10 py-2 m-5 rounded-md w-[fit-content] flex gap-5">
                    <button onClick={() => setIsModalOpen(true)}>
                        Create new task
                    </button>
                </div>
            </div>

            <div className="flex flex-col items-center">
                {filteredTasks.map(task => (
                    <Task
                        key={task.id}
                        task={task}
                        onToggleComplete={handleToggleComplete}
                        onDelete={handleDelete}
                    />
                ))}
            </div>

            <CreateTaskModal
                isOpen={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                onSubmit={handleCreateTask}
            />
        </div>
    );
};

export default TaskList;
