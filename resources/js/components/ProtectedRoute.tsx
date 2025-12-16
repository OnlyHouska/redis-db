import React from 'react';
import { Navigate } from 'react-router-dom';

interface ProtectedRouteProps {
    children: React.ReactNode;
}

/**
 * Protected route wrapper
 *
 * Checks if user is authenticated via localStorage token.
 * Redirects to homepage if not authenticated.
 */
const ProtectedRoute: React.FC<ProtectedRouteProps> = ({ children }) => {
    const token = localStorage.getItem('token');

    if (!token) {
        return <Navigate to="/?login=true" replace />;
    }

    return <>{children}</>;
};

export default ProtectedRoute;
