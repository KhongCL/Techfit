CREATE DATABASE techfit;

USE techfit;

CREATE TABLE User (
    user_id VARCHAR(5) PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    birthday DATE,
    gender ENUM('Male', 'Female'),
    role ENUM('Job Seeker', 'Employer', 'Admin') NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE Job_Seeker (
    job_seeker_id VARCHAR(5) PRIMARY KEY,
    user_id VARCHAR(5) NOT NULL,
    resume BLOB,
    linkedin_link VARCHAR(255),
    job_position_interested VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES User(user_id)
);

CREATE TABLE Employer (
    employer_id VARCHAR(5) PRIMARY KEY,
    user_id VARCHAR(5) NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    linkedin_link VARCHAR(255),
    job_position_interested VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES User(user_id)
);

CREATE TABLE Admin (
    admin_id VARCHAR(5) PRIMARY KEY,
    user_id VARCHAR(5) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User(user_id)
);

CREATE TABLE Assessment_Admin (
    assessment_id VARCHAR(5) PRIMARY KEY,
    admin_id VARCHAR(5) NOT NULL,
    assessment_name VARCHAR(100) NOT NULL,
    assessment_type ENUM('preliminary', 'experience', 'employer_score', 'detailed', 'technical') NOT NULL,
    timestamp DATETIME NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
);

CREATE TABLE Assessment_Job_Seeker (
    assessment_id VARCHAR(5) PRIMARY KEY,
    job_seeker_id VARCHAR(5) NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    score FLOAT,
    summary TEXT,
    feedback TEXT,
    FOREIGN KEY (assessment_id) REFERENCES Assessment_Admin(assessment_id),
    FOREIGN KEY (job_seeker_id) REFERENCES Job_Seeker(job_seeker_id)
);

CREATE TABLE Question (
    question_id VARCHAR(5) PRIMARY KEY,
    assessment_id VARCHAR(5) NOT NULL,
    question_text TEXT NOT NULL,
    answer_type ENUM('multiple choice', 'true/false', 'fill in the blank', 'essay', 'code') NOT NULL,
    FOREIGN KEY (assessment_id) REFERENCES Assessment_Admin(assessment_id)
);

CREATE TABLE Answer (
    answer_id VARCHAR(5) PRIMARY KEY,
    job_seeker_id VARCHAR(5) NOT NULL,
    question_id VARCHAR(5) NOT NULL,
    answer_text TEXT,
    FOREIGN KEY (job_seeker_id) REFERENCES Job_Seeker(job_seeker_id),
    FOREIGN KEY (question_id) REFERENCES Question(question_id)
);

CREATE TABLE Resource (
    resource_id VARCHAR(5) PRIMARY KEY,
    type VARCHAR(50),
    link VARCHAR(255)
);

CREATE TABLE Admin_Resource (
    admin_id VARCHAR(5),
    resource_id VARCHAR(5),
    action_type ENUM('added', 'edited', 'deleted') NOT NULL,
    timestamp DATETIME NOT NULL,
    notes TEXT,
    PRIMARY KEY (admin_id, resource_id),
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id),
    FOREIGN KEY (resource_id) REFERENCES Resource(resource_id)
);

CREATE TABLE Feedback (
    feedback_id VARCHAR(5) PRIMARY KEY,
    user_id VARCHAR(5) NOT NULL,
    text TEXT NOT NULL,
    timestamp DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User(user_id)
);

CREATE TABLE Profile_Setting (
    user_id VARCHAR(5) PRIMARY KEY,
    editable_fields TEXT,
    FOREIGN KEY (user_id) REFERENCES User(user_id)
);

CREATE TABLE Report (
    report_id VARCHAR(5) PRIMARY KEY,
    admin_id VARCHAR(5),
    report_type ENUM('feedback', 'user') NOT NULL,
    description TEXT NOT NULL,
    timestamp DATETIME NOT NULL,
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
);

CREATE TABLE Feedback_Management (
    feedback_management_id VARCHAR(5) PRIMARY KEY,
    feedback_id VARCHAR(5),
    admin_id VARCHAR(5),
    action_type ENUM('reviewed', 'responded', 'resolved') NOT NULL,
    timestamp DATETIME NOT NULL,
    response_text TEXT DEFAULT NULL,
    FOREIGN KEY (feedback_id) REFERENCES Feedback(feedback_id),
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
);

CREATE TABLE User_Audit_Log (
    user_log_id VARCHAR(5) PRIMARY KEY,
    user_id VARCHAR(5),
    admin_id VARCHAR(5),
    action_type ENUM('viewed', 'edited', 'deleted') NOT NULL,
    timestamp DATETIME NOT NULL,
    details TEXT DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES User(user_id),
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
);

CREATE TABLE Assessment_Audit_Log (
    assessment_log_id VARCHAR(5) PRIMARY KEY,
    assessment_id VARCHAR(5),
    admin_id VARCHAR(5),
    action_type ENUM('created', 'edited', 'deleted') NOT NULL,
    timestamp DATETIME NOT NULL,
    details TEXT DEFAULT NULL,
    FOREIGN KEY (assessment_id) REFERENCES Assessment_Admin(assessment_id),
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
);