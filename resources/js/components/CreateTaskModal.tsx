import React, { useState, FormEvent } from 'react';

interface CreateTaskModalProps {
    isOpen: boolean;
    onClose: () => void;
    onSubmit: (taskData: NewTaskData) => Promise<void>;
}

export interface NewTaskData {
    title: string;
    description: string;
    category: string;
    due_date?: string;
}

/**
 * Modal component for creating new tasks
 *
 * Provides a form with validation for task title, category, description, and due date.
 * Resets form state on successful submission or cancel.
 */
const CreateTaskModal: React.FC<CreateTaskModalProps> = ({ isOpen, onClose, onSubmit }) => {
    const [formData, setFormData] = useState<NewTaskData>({
        title: '',
        description: '',
        category: '',
        due_date: ''
    });
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [error, setError] = useState<string>('');

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

    // Update form field values
    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value
        });
    };

    // Validate and submit task
    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();
        setError('');

        if (!formData.title.trim()) {
            setError('Task title is required');
            return;
        }

        if (!formData.category) {
            setError('Please select a category');
            return;
        }

        setIsSubmitting(true);
        try {
            await onSubmit(formData);
            // Reset form on success
            setFormData({ title: '', description: '', category: '', due_date: '' });
            onClose();
        } catch (err) {
            setError('Error creating task. Please try again.');
        } finally {
            setIsSubmitting(false);
        }
    };

    // Reset form and close modal
    const handleClose = () => {
        setFormData({ title: '', description: '', category: '', due_date: '' });
        setError('');
        onClose();
    };

    if (!isOpen) return null;

    return (
        <div className="flex w-full h-[100vh] items-center justify-center fixed -top-20 left-0 ">

            <div
                className="fixed bg-opacity-10 flex items-center justify-center z-50 w-[fit-content] h-[fit-content] border-2 rounded-md backdrop-blur-md py-5 px-8"
            >
                <div onClick={(e) => e.stopPropagation()} className="">
                    <div className="flex flex-row justify-between w-full mb-5">
                        <h2 className="underline font-bold">Create New Task</h2>
                        <button className="font-bold" onClick={handleClose}>x</button>
                    </div>

                    <form onSubmit={handleSubmit}>
                        {error && <div>{error}</div>}

                        <div className="flex gap-3 mb-3 justify-between">
                            <label>Task Title<sup className="text-red-500">*</sup></label>
                            <input
                                type="text"
                                name="title"
                                value={formData.title}
                                onChange={handleChange}
                                placeholder="e.g. Math homework"
                                required
                                className="border-1 px-1"
                            />
                        </div>

                        <div className="flex gap-3 mb-3 justify-between">
                            <label>Category<sup className="text-red-500">*</sup></label>
                            <select
                                name="category"
                                value={formData.category}
                                onChange={handleChange}
                                required
                                className="border-1 px-1"
                            >
                                <option value="">-- Select category --</option>
                                {categories.map(cat => (
                                    <option key={cat} value={cat}>{cat}</option>
                                ))}
                            </select>
                        </div>

                        <div className="flex gap-3 mb-3 justify-between">
                            <label>Description</label>
                            <textarea
                                name="description"
                                value={formData.description}
                                onChange={handleChange}
                                placeholder="Detailed task description..."
                                rows={2}
                                className="border-1 px-1"
                            />
                        </div>

                        <div className="flex gap-3 mb-3 justify-between">
                            <label>Due Date</label>
                            <input
                                type="date"
                                name="due_date"
                                value={formData.due_date}
                                onChange={handleChange}
                                min={new Date().toISOString().split('T')[0]}
                                className="border-1 px-1"
                            />
                        </div>

                        <div className="flex justify-center gap-[40px] mt-10">
                            <button type="button" onClick={handleClose} className="border-1 border-red-500 px-3 py-1 rounded-md hover:border-black hover:text-black hover:bg-red-500 transition-all cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" disabled={isSubmitting} className="border-1 border-green-500 px-3 py-1 rounded-md hover:border-black hover:text-black hover:bg-green-500 transition-all cursor-pointer">
                                {isSubmitting ? 'Creating...' : 'Create Task'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
};

export default CreateTaskModal;
