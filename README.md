# PHP Topic Voting System

A full-featured web application that allows users to create, vote on, and comment on topics, built with PHP and MySQL.

## Features

- **User Authentication**: Secure registration and login system
- **Topic Management**: Create and browse topics
- **Voting System**: Upvote or downvote topics
- **Comments**: Discuss topics with other users
- **User Profiles**: View your activity, including created topics, votes, and comments
- **Leaderboard**: See the most popular topics based on votes
- **Dark/Light Theme**: Toggle between different UI themes

## Tech Stack

- **Backend**: PHP 7.1+
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript
- **Testing**: PHPUnit

## Project Structure

```
├── classes/                # Class definitions
│   ├── Logger.php          # Logging functionality
│   └── TimeFormatter.php   # Time formatting utilities
├── classes.php             # Main class definitions (User, Topic, Vote, Comment)
├── dashboard.php           # User dashboard
├── db.config.example.php   # Database configuration template
├── index.php               # Login page
├── leaderboard.php         # Top voted topics
├── logout.php              # Logout functionality
├── profile.php             # User profile page
├── register.php            # User registration
├── set_theme.php           # Theme switching functionality
├── style.css               # Application styling
├── tables.sql              # Database schema
├── tests/                  # Test directory
│   ├── VotingAppTest.php   # Unit tests
│   ├── phpunit.xml         # PHPUnit configuration
│   └── test.php            # Test runner
└── topics.php              # Topic listing and interaction
```

## Installation

1. Clone the repository:
   ```
   git clone https://github.com/parsamollahoseini/PHP-VotingApp.git
   ```

2. Set up the database:
   - Create a MySQL database
   - Import the schema from `tables.sql`
   - Copy `db.config.example.php` to `db.config.php` and update with your credentials

3. Configure your web server:
   - Point your web server to the project directory
   - Ensure PHP 7.1+ is installed and configured

4. Access the application in your browser

## Configuration

Copy the example configuration file and modify it with your database details:

```php
<?php
return [
    'app' => [
        'host' => 'localhost',
        'username' => 'your_username',
        'password' => 'your_password',
        'dbname' => 'your_database'
    ],
    'test' => [
        'host' => 'localhost',
        'username' => 'test_username',
        'password' => 'test_password',
        'dbname' => 'test_database'
    ],
];
```

## Testing

The application includes comprehensive unit tests using PHPUnit:

1. Install PHPUnit (if not already installed):
   ```
   composer require --dev phpunit/phpunit
   ```

2. Run the tests:
   ```
   cd tests
   php test.php
   ```

## Security Considerations

- Database credentials are stored in a separate configuration file
- Passwords are hashed using PHP's built-in password hashing functions
- Input validation is performed on all user inputs
- Prepared statements are used to prevent SQL injection

## Project Screenshots

### Light Theme
*[Insert screenshot of light theme here]*

### Dark Theme
*[Insert screenshot of dark theme here]*

## Contributors

- [Parsa Mollahoseini](https://github.com/parsamollahoseini)

## License

This project is licensed under the MIT License - see the LICENSE file for details.
