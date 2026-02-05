-- Insert Roles
INSERT IGNORE INTO roles (name) VALUES ('admin'), ('user');

-- Insert Permissions
INSERT IGNORE INTO permissions (name) VALUES 
('users.read'), 
('users.roles.write');

-- Assign Permissions to Admin Role
INSERT IGNORE INTO role_permissions (role_id, permission_id) 
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.name = 'admin' AND p.name IN ('users.read', 'users.roles.write');

-- Insert Admin User (Password: secret123)
-- Hash generated via password_hash('secret123', PASSWORD_BCRYPT)
INSERT IGNORE INTO users (email, password_hash) 
VALUES ('admin@example.com', '$2y$10$iOIwj193ckrkaBlNHareAOkmpmPrKXV3SuRfi1VxToOhlvTr64I3Xm');

-- Assign Admin Role to Admin User
INSERT IGNORE INTO user_roles (user_id, role_id)
SELECT u.id, r.id FROM users u, roles r
WHERE u.email = 'admin@example.com' AND r.name = 'admin';
