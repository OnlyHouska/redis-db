// main.tsx
import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import './axios-config'; // ✅ IMPORT JAKO PRVNÍ!

import Homepage from './pages/Homepage';
import TaskList from './pages/TaskList';
import ProtectedRoute from './components/ProtectedRoute';

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
                <Route
                    path="/tasks"
                    element={
                        <ProtectedRoute>
                            <TaskList />
                        </ProtectedRoute>
                    }
                />
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
