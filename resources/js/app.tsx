import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import axios from 'axios';

import Homepage from './pages/Homepage';
import TaskList from './pages/TaskList';

/**
 * Application entry point
 *
 * Configures axios defaults and sets up routing.
 * Restores JWT token from localStorage if available.
 */

// Configure axios defaults for all requests
axios.defaults.baseURL = 'http://localhost:3000';
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['Content-Type'] = 'application/json';

// Restore authentication token from localStorage
const token = localStorage.getItem('token');
if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

/**
 * Root application component
 *
 * Defines route structure with public homepage and protected task list.
 */
const App = () => {
    return (
        <BrowserRouter>
            <Routes>
                <Route path="/" element={<Homepage />} />
                <Route path="/tasks" element={<TaskList />} />
            </Routes>
        </BrowserRouter>
    );
};

// Mount React application to DOM
const container = document.getElementById('root');
if (container) {
    const root = createRoot(container);
    root.render(<App />);
}
