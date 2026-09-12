ALTER TABLE interesse ADD COLUMN IF NOT EXISTS data_aceite DATETIME NULL;
ALTER TABLE interesse ADD COLUMN IF NOT EXISTS data_recusa DATETIME NULL;
CREATE TABLE IF NOT EXISTS login_tentativa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    criado_em DATETIME NOT NULL,
    INDEX idx_email_criado (email, criado_em),
    INDEX idx_ip_criado (ip, criado_em)
);
ALTER TABLE usuario ADD COLUMN IF NOT EXISTS remember_token VARCHAR(64) NULL;
