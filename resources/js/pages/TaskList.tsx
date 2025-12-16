import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import Task, { TaskData } from '../components/Task';
import CreateTaskModal, { NewTaskData } from '../components/CreateTaskModal';
import { setAuthToken } from "../utils/auth";

/**
 * Task list page component
 *
 * Main task management interface with filtering and notifications.
 * Uses standard HTTP requests without real-time updates.
 */
const TaskList: React.FC = () => {
    const navigate = useNavigate();
    const [tasks, setTasks] = useState<TaskData[]>([]);
    const [filter, setFilter] = useState<string>('all');
    const [categoryFilter, setCategoryFilter] = useState<string>('all');
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [user, setUser] = useState<{ name: string; email: string } | null>(null);
    const [notification, setNotification] = useState<string>('');

    const categories = [
        'Mathematics',
        'Programming',
        'Czech Language',
        'English Language',
        'Physics',
        'Chemistry',
        'History',
        'Other'
    ];

    // Initialize user data and fetch tasks
    useEffect(() => {
        const userData = localStorage.getItem('user');
        if (userData) {
            setUser(JSON.parse(userData));
        }
        fetchTasks();
    }, []);

    // Fetch tasks from API
    const fetchTasks = async () => {
        try {
            const response = await axios.get<TaskData[]>('/api/tasks');
            setTasks(response.data);
        } catch (error) {
            console.error('Error loading tasks:', error);
        }
    };

    // Display temporary notification (auto-hide after 4s)
    const showNotification = (message: string, type: 'success' | 'info' | 'error' = 'success') => {
        setNotification(message);
        setTimeout(() => setNotification(''), 4000);
    };

    const handleCreateTask = async (taskData: NewTaskData) => {
        try {
            await axios.post(`/api/tasks/create`, taskData);
            await fetchTasks();
            showNotification('Task created successfully', 'success');
        } catch (error) {
            console.error('Error creating task:', error);
            showNotification('Failed to create task', 'error');
        }
    };

    const handleToggleComplete = async (id: number) => {
        try {
            await axios.put(`/api/tasks/${id}/toggle`);
            await fetchTasks();
            showNotification('Task updated', 'success');
        } catch (error) {
            console.error('Error updating task:', error);
            showNotification('Failed to update task', 'error');
        }
    };

    const handleDelete = async (id: number) => {
        try {
            await axios.delete(`/api/tasks/${id}/delete`);
            setTasks(tasks.filter(task => task.id !== id));
            showNotification('Task deleted', 'info');
        } catch (error) {
            console.error('Error deleting task:', error);
            showNotification('Failed to delete task', 'error');
        }
    };

    const handleLogout = async () => {
        try {
            await axios.post('/api/auth/logout');
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            setAuthToken(null);
            localStorage.removeItem('user');
            navigate('/');
        }
    };

    // Apply status and category filters
    const filteredTasks = tasks.filter(task => {
        if (filter === 'completed' && !task.completed) return false;
        if (filter === 'pending' && task.completed) return false;
        if (categoryFilter !== 'all' && task.category !== categoryFilter) return false;
        return true;
    });

    return (
        <div className="w-full">
            {/* Notification toast */}
            {notification && (
                <div className="fixed top-5 right-5 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-slide-in max-w-xs md:max-w-md">
                    {notification}
                </div>
            )}

            {/* Header with user info and logout */}
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center px-5 py-3 border-b-2 border-black gap-3">
                <div className="flex flex-col md:flex-row gap-2 md:gap-3 md:items-center">
                    <span className="font-semibold">Welcome, {user?.name || 'User'}!</span>
                    <span className="text-gray-500 text-sm">{user?.email}</span>
                </div>

                <button
                    onClick={handleLogout}
                    className="border-2 border-black px-4 py-1 rounded-md hover:bg-red-100 transition-colors cursor-pointer"
                >
                    Logout
                </button>
            </div>

            {/* Task statistics */}
            <div className="px-5 py-3 bg-gray-50 border-b border-gray-300">
                <div className="flex flex-wrap gap-4 md:gap-6 text-sm">
                    <span>Total: <strong>{tasks.length}</strong></span>
                    <span>Active: <strong>{tasks.filter(t => !t.completed).length}</strong></span>
                    <span>Completed: <strong>{tasks.filter(t => t.completed).length}</strong></span>
                </div>
            </div>

            {/* Filters and actions */}
            <div className="w-full px-5">
                <div className="flex flex-col md:flex-row gap-3 md:gap-5 my-5">
                    {/* Status filter */}
                    <div className="border-2 border-black px-6 md:px-10 py-2 rounded-md flex gap-3 md:gap-5 justify-center">
                        <button className={filter=="all"?'underline cursor-pointer':'cursor-pointer'} onClick={() => setFilter('all')}>All</button>
                        <button className={filter=="pending"?'underline cursor-pointer':'cursor-pointer'} onClick={() => setFilter('pending')}>Active</button>
                        <button className={filter=="completed"?'underline cursor-pointer':'cursor-pointer'} onClick={() => setFilter('completed')}>Completed</button>
                    </div>

                    {/* Category filter */}
                    <div className="border-2 border-black px-6 md:px-10 py-2 rounded-md flex justify-center">
                        <select
                            value={categoryFilter}
                            onChange={(e) => setCategoryFilter(e.target.value)}
                            className="cursor-pointer bg-transparent outline-none w-full md:w-auto"
                        >
                            <option value="all">All Categories</option>
                            {categories.map(cat => (
                                <option key={cat} value={cat}>{cat}</option>
                            ))}
                        </select>
                    </div>

                    {/* Create button */}
                    <div className="border-2 border-black px-6 md:px-10 py-2 rounded-md flex justify-center">
                        <button onClick={() => setIsModalOpen(true)} className="cursor-pointer">
                            Create new task
                        </button>
                    </div>
                </div>

                {/* Tasks list */}
                <div className="flex flex-col items-start md:items-center w-full">
                    {filteredTasks.length === 0 ? (
                        <p className="text-gray-500 mt-10">No tasks found</p>
                    ) : (
                        filteredTasks.map(task => (
                            <Task
                                key={task.id}
                                task={task}
                                onToggleComplete={handleToggleComplete}
                                onDelete={handleDelete}
                            />
                        ))
                    )}
                </div>
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
