import React from 'react';
import { createRoot } from 'react-dom/client';
import TaskList from './components/TaskList';
import axios from 'axios';

axios.defaults.baseURL = 'http://localhost:8080';
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['Content-Type'] = 'application/json';

// CSRF token pro Laravel
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
}

const container = document.getElementById('root');
if (container) {
    const root = createRoot(container);
    root.render(<TaskList />);
}
