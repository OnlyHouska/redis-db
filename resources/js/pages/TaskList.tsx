import React, { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import Task, { TaskData } from '../components/Task';
import CreateTaskModal, { NewTaskData } from '../components/CreateTaskModal';

/**
 * Task list page component
 *
 * Main task management interface with filtering, real-time polling updates,
 * and notifications. Polls server every 2 seconds for task changes.
 */
const TaskList: React.FC = () => {
    const navigate = useNavigate();
    const [tasks, setTasks] = useState<TaskData[]>([]);
    const [filter, setFilter] = useState<string>('all');
    const [categoryFilter, setCategoryFilter] = useState<string>('all');
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [user, setUser] = useState<{ name: string; email: string } | null>(null);
    const [notification, setNotification] = useState<string>('');

    // Track previous task state to detect changes without causing re-renders
    const lastTaskIdsRef = useRef<Set<number>>(new Set());
    const lastTaskStatesRef = useRef<Map<number, boolean>>(new Map());

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

    // Initialize user data and start polling
    useEffect(() => {
        const userData = localStorage.getItem('user');
        if (userData) {
            setUser(JSON.parse(userData));
        }
        fetchTasks();

        // Poll for task updates every 2 seconds
        const pollInterval = setInterval(() => {
            fetchTasksQuietly();
        }, 2000);

        return () => {
            clearInterval(pollInterval);
        };
    }, []);

    // Initial task fetch with error handling
    const fetchTasks = async () => {
        try {
            const response = await axios.get<TaskData[]>('/api/tasks');
            setTasks(response.data);
            lastTaskIdsRef.current = new Set(response.data.map(t => t.id));

            const stateMap = new Map<number, boolean>();
            response.data.forEach(t => stateMap.set(t.id, t.completed));
            lastTaskStatesRef.current = stateMap;
        } catch (error) {
            console.error('Error loading tasks:', error);
        }
    };

    // Silent polling - detects new, deleted, and state-changed tasks
    const fetchTasksQuietly = async () => {
        try {
            const response = await axios.get<TaskData[]>('/api/tasks');
            const currentTaskIds = new Set(response.data.map(t => t.id));

            // Detect new tasks
            const newTaskIds = [...currentTaskIds].filter(id => !lastTaskIdsRef.current.has(id));
            if (newTaskIds.length > 0) {
                const newTask = response.data.find(t => t.id === newTaskIds[0]);
                if (newTask) {
                    showNotification(`New task created: ${newTask.title}`, 'success');
                }
            }

            // Detect deleted tasks
            const deletedTaskIds = [...lastTaskIdsRef.current].filter(id => !currentTaskIds.has(id));
            if (deletedTaskIds.length > 0) {
                showNotification(`Task deleted`, 'info');
            }

            // Detect completion state changes
            response.data.forEach(task => {
                const previousState = lastTaskStatesRef.current.get(task.id);
                if (previousState !== undefined && previousState !== task.completed) {
                    if (task.completed) {
                        showNotification(`Task completed: ${task.title}`, 'success');
                    } else {
                        showNotification(`Task reactivated: ${task.title}`, 'info');
                    }
                }
            });

            // Update state and tracking refs
            setTasks(response.data);
            lastTaskIdsRef.current = currentTaskIds;

            const stateMap = new Map<number, boolean>();
            response.data.forEach(t => stateMap.set(t.id, t.completed));
            lastTaskStatesRef.current = stateMap;
        } catch (error) {
            // Silent fail - don't disrupt user experience during polling
        }
    };

    // Display temporary notification (auto-hide after 4s)
    const showNotification = (message: string, type: 'success' | 'info' | 'error' = 'success') => {
        setNotification(message);
        setTimeout(() => setNotification(''), 4000);
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
            // Optimistic UI update
            setTasks(tasks.filter(task => task.id !== id));
            lastTaskIdsRef.current.delete(id);
            lastTaskStatesRef.current.delete(id);
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
            // Clear local auth data and redirect
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            delete axios.defaults.headers.common['Authorization'];
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
