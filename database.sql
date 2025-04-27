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
    Role ENUM('admin','program head', 'faculty', 'registrar', 'student') NOT NULL
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

INSERT INTO Course (CourseName)
VALUES
('Bachelor of Science in Education Major in MAPEH'),
('Bachelor of Science in Education Major in English'),
('Bachelor of Science in Education Major in Science'),
('Bachelor of Science in Education Major in Math'),
('Bachelor of Science in Entrepreneurship');

CREATE TABLE YearLevel (
    YearLevelID INT AUTO_INCREMENT PRIMARY KEY,
    YearLevelName VARCHAR(50) NOT NULL
);

INSERT INTO YearLevel (YearLevelName)
VALUES
('First Year'),
('Second Year'),
('Third Year'),
('Fourth Year');

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

CREATE TABLE Events (
    EventID INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(255) NOT NULL,
    Description TEXT,
    StartDate DATETIME NOT NULL,
    EndDate DATETIME NOT NULL,
    Location VARCHAR(255),
    EventType VARCHAR(50) NOT NULL,
    Color VARCHAR(20) DEFAULT '#4361ee',
    IsAllDay BOOLEAN DEFAULT FALSE,
    CreatedBy INT,
    DateCreated DATETIME DEFAULT CURRENT_TIMESTAMP,
    LastModified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CreatedBy) REFERENCES Users(UserID)
);

INSERT INTO Events 
(Title, Description, StartDate, EndDate, Location, EventType, Color, IsAllDay, CreatedBy)
VALUES
('Faculty Meeting', 'Monthly meeting to discuss academic matters.', '2025-04-20 09:00:00', '2025-04-20 11:00:00', 'Main Conference Hall', 'Meeting', '#ff6b6b', FALSE, 1),
('Student Orientation', 'Orientation program for incoming freshmen.', '2025-05-01 08:00:00', '2025-05-01 17:00:00', 'Auditorium', 'Orientation', '#1dd1a1', FALSE, 2),
('Holiday - Independence Day', 'School closed in observance of Independence Day.', '2025-06-12 00:00:00', '2025-06-12 23:59:59', '', 'Holiday', '#feca57', TRUE, 3),
('Final Exams Begin', 'Start of final exams for the semester.', '2025-05-15 08:00:00', '2025-05-15 17:00:00', 'Various Classrooms', 'Exam', '#5f27cd', FALSE, 4),
('System Maintenance', 'Scheduled system maintenance window.', '2025-04-25 22:00:00', '2025-04-26 02:00:00', 'IT Department', 'Maintenance', '#576574', FALSE, 2);

CREATE TABLE Subjects (
    SubjectID INT AUTO_INCREMENT PRIMARY KEY,
    SubjectCode VARCHAR(20) NOT NULL UNIQUE,
    SubjectName VARCHAR(100) NOT NULL,
    Units INT NOT NULL,
    CourseID INT NOT NULL,
    YearLevelID INT NOT NULL,
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID),
    FOREIGN KEY (YearLevelID) REFERENCES YearLevel(YearLevelID)
);

CREATE TABLE Students (
    StudentID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    YearLevelID INT NOT NULL,
    CourseID INT NOT NULL,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (YearLevelID) REFERENCES YearLevel(YearLevelID),
    FOREIGN KEY (CourseID) REFERENCES Course(CourseID),
    UNIQUE (UserID)
);

