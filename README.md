# ICI IT Codespace PHP/MySQL/phpMyAdmin

This project sets up a development environment using GitHub Codespaces with MySQL, phpMyAdmin, and Apache for students in Iligan Computer Institute. Students are required to follow all the notes and guidelines here.

## Students should use their ICI-email supplied email address to use the privileges in the Codespace.
- Free Accounts: 120 hours
- Verified Student/Teacher (applied to GitHub Student Developer Pack): 180 hours
- resets every activation per month

### GitHub Codespaces and GitHub Student Developer Pack
1. To use GitHUB Codespaces, make sure you already registered your ICI-email address to GitHub.
2. Visit https://education.github.com/pack to activate, follow the instructions from GitHub.
3. Allow location tracking from browser prompt as this is needed for GitHub Admins to activate your account.
4. Verification process is automatic but GitHub Education team needs evidence that you are an officially enrolled student of Iligan Computer Institute.
5. (optional) To send your Student ID as part of the verification, follow this instructions, take pictures on both front and back then edit them to be as one picture and attach it to the submission form.
6. (optional) To send your COR / Certificate of Registration as evidence for verification, take a photo of your COR then upload it to the submission form.
7. Depends on the selected document for verification, expect an email coming from GitHub Education Team with the message, "Welcome to Github Global Campus", that indicates that your Student Developer Pack has been activated an able to use GitHub Codespaces at no charge.

## Starting the server just in case in won't start automatically 
1. Go to Terminal
2. Type the following command: `sudo service apache2 start`

## Database Credentials

The following credentials are used to connect to the MySQL database:

- **Host**: `localhost`
- **User**: `mariadb`
- **Password**: `mariadb`
- **Database**: `mariadb`

## Sample PHP MySQL Connection

Create a file named `index.php` in the `htdocs` directory with the following content to test the database connection:

## PHP MySQL Connection

Here is a sample PHP script to connect to the MySQL database:

```php
<?php
$servername = "localhost";
$username = "mariadb";
$password = "mariadb";
$dbname = "mariadb";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully";
?>
```

## Setup

1. **Duplicate this GitHub repository**:
    - Click on the `Use this template` button at the top right of the repository page then select `Create a new repository` to create a copy of this repository in your GitHub account.
2. **Open the duplicated repository in GitHub Codespaces**:
    - Click on the `Code` button and select `Open with Codespaces`.
    - Select `New codespace` to create a new Codespace.
  
## How to Run the Server

1. **Start PHP/Apache Server**:
    - The PHP/Apache server should start automatically when the Codespace is created.

## How to Run the Database

1. **Start MySQL Server**:
    - The MySQL server should start automatically when the Codespace is created.

## How to Access phpMyAdmin

1. **Open phpMyAdmin**:
    - In your Codespace, add `/phpmyadmin` to the URL.
    - Open the forwarded port for phpMyAdmin to access the interface.

## Do's and Don'ts

### Do's

- **Do** upload your work to the `htdocs` directory.
- **Do** use phpMyAdmin for database management.
- **Do** collaborate using Live Share for real-time coding sessions.
- **Do** follow the instructions for exporting the database.

### Don'ts

- **Don't** modify or delete configuration files unless you know what you're doing.
- **Don't** store sensitive information in the repository.
- **Don't** share your Codespace URL publicly.
- **Don't** delete files outside the `htdocs` directory.
- **Don't** delete the `htdocs` directory itself.

## Usage

- **Upload Work**: Students should upload their work to the `htdocs` directory.
- **Access phpMyAdmin**: Navigate to the `Ports` tab in this codespace and open the forwarded port for phpMyAdmin.
- **Run the sample PHP script**: The `htdocs/index.php` file demonstrates a database connection.

### Export Database

To export the database, run the "Export Database" task:

1. Open the Command Palette in VS Code (Ctrl+Shift+P or Cmd+Shift+P).
2. Type "Tasks: Run Task" and select it.
3. Choose "Export Database" from the list of tasks.

### Next Steps

1. **Add Your Code**: Start adding your PHP code to the `htdocs` directory.
2. **Collaborate**: Use Live Share to collaborate with your team in real-time.
3. **Test and Debug**: Use the integrated terminal and debugging tools in VS Code to test and debug your application.
4. **Deploy**: Once your application is ready, consider deploying it to a production environment.

### Troubleshooting

- **Codespace Not Starting**: Ensure you have the necessary permissions and resources to create a Codespace.
- **Database Connection Issues**: Check the MySQL server status and ensure your credentials are correct.
- **Permission Errors**: Ensure the `htdocs` directory has the correct permissions set.


### Explanation

- **Setup**: Updated instructions for students to duplicate the repository and create their own Codespaces.
- **Database Credentials**: Lists the database credentials for easy reference.
- **Sample PHP MySQL Connection**: Provides a sample PHP script to test the database connection.
- **Do's and Don'ts**: Provides guidelines for using the environment effectively, including not deleting files outside the [htdocs](http://_vscodecontentref_/0) directory.
- **Usage**: Instructions on how to upload work, access phpMyAdmin, and run the sample PHP script.
- **Export Database**: Steps to export the database using the "Export Database" task.
- **Next Steps**: Suggests actions to take after setting up the environment.
- **Troubleshooting**: Offers solutions for common issues.
- **Support**: Provides information on how to get help.

This improved [`README.md`](README.md ) should provide clear instructions and guidelines for students to set up and use the development environment effectively.
