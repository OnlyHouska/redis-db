import React, { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import Task, { TaskData } from '../components/Task';
import CreateTaskModal, { NewTaskData } from '../components/CreateTaskModal';

const TaskList: React.FC = () => {
    const navigate = useNavigate();
    const [tasks, setTasks] = useState<TaskData[]>([]);
    const [filter, setFilter] = useState<string>('all');
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [user, setUser] = useState<{ name: string; email: string } | null>(null);
    const [notification, setNotification] = useState<string>('');

    // Use useRef instead of useState for tracking IDs - doesn't trigger re-renders
    const lastTaskIdsRef = useRef<Set<number>>(new Set());

    useEffect(() => {
        const userData = localStorage.getItem('user');
        if (userData) {
            setUser(JSON.parse(userData));
        }
        fetchTasks();

        const pollInterval = setInterval(() => {
            fetchTasksQuietly();
        }, 2000);

        return () => {
            clearInterval(pollInterval);
        };
    }, []);

    const fetchTasks = async () => {
        try {
            const response = await axios.get<TaskData[]>('/api/tasks');
            setTasks(response.data);
            lastTaskIdsRef.current = new Set(response.data.map(t => t.id));
        } catch (error) {
            console.error('Error loading tasks:', error);
        }
    };

    const fetchTasksQuietly = async () => {
        try {
            const response = await axios.get<TaskData[]>('/api/tasks');
            const currentTaskIds = new Set(response.data.map(t => t.id));

            // Find new task IDs
            const newTaskIds = [...currentTaskIds].filter(id => !lastTaskIdsRef.current.has(id));

            if (newTaskIds.length > 0) {
                const newTask = response.data.find(t => t.id === newTaskIds[0]);
                if (newTask) {
                    setNotification(`New task created: ${newTask.title}`);
                    setTimeout(() => setNotification(''), 5000);
                }
            }

            setTasks(response.data);
            lastTaskIdsRef.current = currentTaskIds;
        } catch (error) {
            // Silent fail for polling errors
        }
    };

    const handleCreateTask = async (taskData: NewTaskData) => {
        await axios.post(`/api/tasks/create`, taskData);
        await fetchTasks();
    };

    const handleToggleComplete = async (id: number) => {
        try {
            await axios.put(`/api/tasks/${id}/toggle`);
            await fetchTasksQuietly();
        } catch (error) {
            console.error('Error updating task:', error);
        }
    };

    const handleDelete = async (id: number) => {
        try {
            await axios.delete(`/api/tasks/${id}/delete`);
            setTasks(tasks.filter(task => task.id !== id));
            lastTaskIdsRef.current.delete(id);
        } catch (error) {
            console.error('Error deleting task:', error);
        }
    };

    const handleLogout = async () => {
        try {
            await axios.post('/api/auth/logout');
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            delete axios.defaults.headers.common['Authorization'];
            navigate('/');
        }
    };

    const filteredTasks = tasks.filter(task => {
        if (filter === 'all') return true;
        if (filter === 'completed') return task.completed;
        if (filter === 'pending') return !task.completed;
        return task.category === filter;
    });

    return (
        <div className="w-full">
            {notification && (
                <div className="fixed top-5 right-5 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
                    {notification}
                </div>
            )}

            <div className="flex justify-between items-center px-5 py-3 border-b-2 border-black">
                <div className="flex gap-3 items-center">
                    <span className="font-semibold">Welcome, {user?.name || 'User'}!</span>
                    <span className="text-gray-500 text-sm">{user?.email}</span>
                </div>

                <div className="flex gap-3">
                    <button
                        onClick={handleLogout}
                        className="border-2 border-black px-4 py-1 rounded-md hover:bg-red-100 transition-colors cursor-pointer"
                    >
                        Logout
                    </button>
                </div>
            </div>

            <div className="px-5 py-3 bg-gray-50 border-b border-gray-300">
                <div className="flex gap-6 text-sm">
                    <span>Total: <strong>{tasks.length}</strong></span>
                    <span>Active: <strong>{tasks.filter(t => !t.completed).length}</strong></span>
                    <span>Completed: <strong>{tasks.filter(t => t.completed).length}</strong></span>
                </div>
            </div>

            <div className="w-[fit-content]">
                <div className="flex flex-row gap-5">
                    <div className="border-2 border-black px-10 py-2 m-5 rounded-md w-[fit-content] flex gap-5">
                        <button className={filter=="all"?'underline cursor-pointer':'cursor-pointer'} onClick={() => setFilter('all')}>All</button>
                        <button className={filter=="pending"?'underline cursor-pointer':'cursor-pointer'} onClick={() => setFilter('pending')}>Active</button>
                        <button className={filter=="completed"?'underline cursor-pointer':'cursor-pointer'} onClick={() => setFilter('completed')}>Completed</button>
                    </div>

                    <div className="border-2 border-black px-10 py-2 m-5 rounded-md w-[fit-content] flex gap-5">
                        <button onClick={() => setIsModalOpen(true)} className="cursor-pointer">
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
        </div>
    );
};

export default TaskList;
