-- CareerAccess: Custom Accessibility Settings
-- Run this once in MySQL Workbench after selecting jobtrackerdb.
USE jobtrackerdb;

CREATE TABLE IF NOT EXISTS AccessibilitySettings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    font_scale ENUM('small','normal','large','extra_large') NOT NULL DEFAULT 'normal',
    high_contrast TINYINT(1) NOT NULL DEFAULT 0,
    dyslexia_font TINYINT(1) NOT NULL DEFAULT 0,
    reduce_motion TINYINT(1) NOT NULL DEFAULT 0,
    large_cursor TINYINT(1) NOT NULL DEFAULT 0,
    increased_spacing TINYINT(1) NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_accessibility_user
        FOREIGN KEY (user_id) REFERENCES Users(user_id)
        ON DELETE CASCADE
);
