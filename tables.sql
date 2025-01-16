CREATE TABLE Users (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       username VARCHAR(100) NOT NULL UNIQUE,
                       email VARCHAR(100) NOT NULL UNIQUE,
                       password VARCHAR(255) NOT NULL,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE Topics (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        title VARCHAR(100) NOT NULL,
                        description TEXT NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE ON UPDATE CASCADE
);


CREATE TABLE Votes (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       user_id INT NOT NULL,
                       topic_id INT NOT NULL,
                       vote_type ENUM('up', 'down') NOT NULL,
                       voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE ON UPDATE CASCADE,
                       FOREIGN KEY (topic_id) REFERENCES Topics(id) ON DELETE CASCADE ON UPDATE CASCADE
);


CREATE TABLE Comments (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          user_id INT NOT NULL,
                          topic_id INT NOT NULL,
                          comment TEXT NOT NULL,
                          commented_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE ON UPDATE CASCADE,
                          FOREIGN KEY (topic_id) REFERENCES Topics(id) ON DELETE CASCADE ON UPDATE CASCADE
);


CREATE INDEX idx_username ON Users(username);
CREATE INDEX idx_email ON Users(email);
SET FOREIGN_KEY_CHECKS = 0;