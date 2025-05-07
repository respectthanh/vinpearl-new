#!/usr/bin/env python3
import os
import subprocess
import mysql.connector
from mysql.connector import Error

def initialize_database():
    """Initialize the MySQL database using schema.sql and mock_data.sql files."""
    # Get database credentials (in production, use environment variables or a config file)
    host = input("Enter MySQL host (default: localhost): ") or "localhost"
    user = input("Enter MySQL username: ")
    password = input("Enter MySQL password: ")
    
    # Connect to MySQL server
    try:
        # First connect without selecting a database
        connection = mysql.connector.connect(
            host=host,
            user=user,
            password=password
        )
        
        if connection.is_connected():
            print("Connected to MySQL server")
            
            # Execute the schema.sql file
            execute_sql_file('schema.sql', host, user, password)
            
            # Generate mock data if it doesn't exist
            if not os.path.exists('mock_data.sql'):
                print("Generating mock data...")
                generate_mock_data()
                
            # Execute mock_data.sql to populate the database
            execute_sql_file('mock_data.sql', host, user, password, 'vinpearl_resort')
            
            print("Database initialization completed successfully!")
            
    except Error as e:
        print(f"Error: {e}")
    finally:
        if connection and connection.is_connected():
            connection.close()
            print("MySQL connection closed")

def execute_sql_file(filename, host, user, password, database=None):
    """Execute a SQL file using the mysql command line client."""
    print(f"Executing {filename}...")
    
    cmd = ['mysql', f'--host={host}', f'--user={user}', f'--password={password}']
    if database:
        cmd.append(f'--database={database}')
    
    with open(os.devnull, 'w') as devnull:
        result = subprocess.run(
            cmd,
            stdin=open(filename, 'r'),
            stdout=devnull,
            stderr=subprocess.PIPE,
            text=True
        )
    
    if result.returncode != 0:
        print(f"Error executing {filename}: {result.stderr}")
        exit(1)
    else:
        print(f"Successfully executed {filename}")

def generate_mock_data():
    """Generate mock data by running the mock_data.py script."""
    try:
        subprocess.run(['python', 'mock_data.py'], check=True)
        print("Mock data generated successfully")
    except subprocess.CalledProcessError as e:
        print(f"Error generating mock data: {e}")
        exit(1)

if __name__ == "__main__":
    # Change to the directory containing the script
    os.chdir(os.path.dirname(os.path.abspath(__file__)))
    initialize_database() 