import React, { useState } from 'react';
import LoginModal from '../components/LoginModal';
import RegisterModal from '../components/RegisterModal';

const HomePage: React.FC = () => {
    const [isLoginOpen, setIsLoginOpen] = useState(false);
    const [isRegisterOpen, setIsRegisterOpen] = useState(false);

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

            <LoginModal isOpen={isLoginOpen} onClose={() => setIsLoginOpen(false)} />
            <RegisterModal isOpen={isRegisterOpen} onClose={() => setIsRegisterOpen(false)} />
        </>
    );
};

export default HomePage;
