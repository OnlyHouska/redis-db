import './bootstrap';
import React from 'react';
import { createRoot } from 'react-dom/client';
import TaskList from './components/TaskList';
import axios from 'axios';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
}

const container = document.getElementById('root');
if (container) {
    const root = createRoot(container);
    root.render(<TaskList />);
}
