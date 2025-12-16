import React, { useState, FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

interface RegisterModalProps {
    isOpen: boolean;
    onClose: () => void;
}

const RegisterModal: React.FC<RegisterModalProps> = ({ isOpen, onClose }) => {
    const navigate = useNavigate();
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        password: ''
    });
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [error, setError] = useState<string>('');

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value
        });
    };

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();
        setError('');

        if (formData.password.length < 6) {
            setError('Password must be at least 6 characters');
            return;
        }

        setIsSubmitting(true);

        try {
            console.log('Sending registration data:', formData); // DEBUG
            const response = await axios.post('/api/auth/register', formData);
            console.log('Registration response:', response.data); // DEBUG

            localStorage.setItem('token', response.data.token);
            localStorage.setItem('user', JSON.stringify(response.data.user));
            setFormData({ name: '', email: '', password: '' });
            onClose();
            navigate('/tasks');
        } catch (err: any) {
            console.error('Registration error:', err.response); // DEBUG
            setError(err.response?.data?.error || err.response?.data?.message || 'Registration failed. Please try again.');
        } finally {
            setIsSubmitting(false);
        }
    };


    const handleClose = () => {
        setFormData({ name: '', email: '', password: '' });
        setError('');
        onClose();
    };

    if (!isOpen) return null;

    return (
        <div className="flex w-full h-[100vh] items-center justify-center fixed -top-20 left-0">
            <div className="fixed bg-opacity-10 flex items-center justify-center z-50 w-[fit-content] h-[fit-content] border-2 rounded-md backdrop-blur-md py-5 px-8">
                <div onClick={(e) => e.stopPropagation()}>
                    <div className="flex flex-row justify-between w-full mb-5">
                        <h2 className="underline font-bold">Register</h2>
                        <button className="font-bold" onClick={handleClose}>x</button>
                    </div>

                    <form onSubmit={handleSubmit}>
                        {error && <div className="text-red-500 mb-3">{error}</div>}

                        <div className="flex gap-3 mb-3 justify-between">
                            <label>Name<sup className="text-red-500">*</sup></label>
                            <input
                                type="text"
                                name="name"
                                value={formData.name}
                                onChange={handleChange}
                                placeholder="John Doe"
                                maxLength={255}
                                required
                                className="border-1 px-1"
                            />
                        </div>

                        <div className="flex gap-3 mb-3 justify-between">
                            <label>Email<sup className="text-red-500">*</sup></label>
                            <input
                                type="email"
                                name="email"
                                value={formData.email}
                                onChange={handleChange}
                                placeholder="your@email.com"
                                required
                                className="border-1 px-1"
                            />
                        </div>

                        <div className="flex gap-3 mb-3 justify-between">
                            <label>Password<sup className="text-red-500">*</sup></label>
                            <input
                                type="password"
                                name="password"
                                value={formData.password}
                                onChange={handleChange}
                                placeholder="••••••••"
                                minLength={6}
                                required
                                className="border-1 px-1"
                            />
                        </div>

                        <div className="flex justify-center gap-[40px] mt-10">
                            <button
                                type="button"
                                onClick={handleClose}
                                className="border-1 border-red-500 px-3 py-1 rounded-md hover:border-black hover:text-black hover:bg-red-500 transition-all cursor-pointer"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={isSubmitting}
                                className="border-1 border-green-500 px-3 py-1 rounded-md hover:border-black hover:text-black hover:bg-green-500 transition-all cursor-pointer"
                            >
                                {isSubmitting ? 'Creating...' : 'Register'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
};

export default RegisterModal;
