DROP DATABASE IF EXISTS CCCDB;
CREATE DATABASE CCCDB;

USE CCCDB;

CREATE TABLE Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    FirstName VARCHAR(50) NOT NULL,
    MiddleInitial CHAR(1),
    LastName VARCHAR(50) NOT NULL,
    IDNumber VARCHAR(100) NOT NULL UNIQUE,
    Birthday DATE NOT NULL,
    Email VARCHAR(100) NOT NULL,
    AddressDetails VARCHAR(255),
    PhoneNumber VARCHAR(11),
    Gender ENUM('Male', 'Female') NOT NULL,
    Photo MEDIUMBLOB,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Role ENUM('admin', 'faculty', 'registrar', 'student') NOT NULL
);

INSERT INTO Users 
(IDNumber, FirstName, MiddleInitial, LastName, Birthday, Email, AddressDetails, PhoneNumber, Gender, Photo, Username, Password, Role) 
VALUES  
('02000279465', 'Allan', 'A', 'Aboga-a', STR_TO_DATE('01-15-1995', '%m-%d-%Y'), 'allan.abogaa@ccc.edu', 'Naga City','09296529697','Male', NULL, 'allan', 'password123', 'faculty'),
('02000279466', 'Ernie Joseph', 'B', 'Cledera', STR_TO_DATE('04-09-1998', '%m-%d-%Y'), 'ernie.cledera@ccc.edu', 'Naga City','09296529698','Female', NULL, 'ernie', 'password123', 'admin'),
('02000279467', 'Chrystian Ray', 'C', 'Festin', STR_TO_DATE('09-30-1997', '%m-%d-%Y'), 'chrystian.festin@ccc.edu', 'Naga City','09296529699','Male', NULL, 'chrystian', 'password123', 'registrar'),
('02000279468', 'Joshua Gabriel', 'S', 'Gamora', STR_TO_DATE('04-10-2001', '%m-%d-%Y'), 'joshua.gamora@ccc.edu', 'Naga City','09296529690','Male', NULL, 'joshua', 'password123', 'student');

CREATE TABLE Course (
    CourseID INT AUTO_INCREMENT PRIMARY KEY,
    CourseName VARCHAR(100) NOT NULL
);

CREATE TABLE YearLevel (
    YearLevelID INT AUTO_INCREMENT PRIMARY KEY,
    YearLevelName VARCHAR(50) NOT NULL
);

CREATE TABLE ContactSupportSubmissions (
    TicketNumber INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL,
    Issue TEXT NOT NULL,
    SubmittedAt DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ForgotPasswordSubmissions (
    SubmissionID INT AUTO_INCREMENT PRIMARY KEY,
    PhoneNumber VARCHAR(11) NOT NULL,
    IDNumber VARCHAR(100),
    FOREIGN KEY (IDNumber) REFERENCES Users(IDNumber)
);