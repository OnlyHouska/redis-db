// resources/js/pages/Homepage.tsx
import React, { useState, useEffect } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import LoginModal from '../components/LoginModal';
import RegisterModal from '../components/RegisterModal';

/**
 * Landing page component
 *
 * Displays login and register buttons that open their respective modal dialogs.
 * Serves as the entry point for unauthenticated users.
 * Automatically opens login modal if redirected from protected route.
 */
const HomePage: React.FC = () => {
    const [searchParams, setSearchParams] = useSearchParams();
    const navigate = useNavigate();
    const [isLoginOpen, setIsLoginOpen] = useState(false);
    const [isRegisterOpen, setIsRegisterOpen] = useState(false);

    useEffect(() => {
        // Check if user is already logged in
        const token = localStorage.getItem('token');
        if (token) {
            navigate('/tasks');
            return;
        }

        // Open login modal if redirected from protected route
        if (searchParams.get('login') === 'true') {
            setIsLoginOpen(true);
            // Remove query parameter from URL
            setSearchParams({});
        }
    }, [searchParams, navigate, setSearchParams]);

    return (
        <>
            <div className="flex items-center justify-center min-h-screen">
                <div className="flex flex-col gap-5">
                    <button
                        onClick={() => setIsLoginOpen(true)}
                        className="border-2 border-black px-10 py-2 rounded-md w-full hover:bg-gray-100 transition-all cursor-pointer"
                    >
                        Login
                    </button>

                    <button
                        onClick={() => setIsRegisterOpen(true)}
                        className="border-2 border-black px-10 py-2 rounded-md w-full hover:bg-gray-100 transition-all cursor-pointer"
                    >
                        Register
                    </button>
                </div>
            </div>

            {/* Authentication modals - controlled by state */}
            <LoginModal isOpen={isLoginOpen} onClose={() => setIsLoginOpen(false)} />
            <RegisterModal isOpen={isRegisterOpen} onClose={() => setIsRegisterOpen(false)} />
        </>
    );
};

export default HomePage;
