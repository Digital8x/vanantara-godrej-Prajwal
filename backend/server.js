const cron = require('node-cron');
const mysql = require('mysql2/promise');
const nodemailer = require('nodemailer');

// Database Configuration
const dbConfig = {
    host: 'localhost',
    user: 'a1679hju_GodrejPrajwal',
    password: 'ArjunEswar',
    database: 'a1679hju_GodrejPrajwal'
};

// Email Configuration (Configure SMTP here)
const transporter = nodemailer.createTransport({
    host: 'smtp.example.com', // Replace with actual SMTP
    port: 587,
    secure: false,
    auth: {
        user: 'admin@example.com',
        pass: 'password123'
    }
});

const adminEmail = 'admin@example.com'; // Change to actual admin email

// Scheduled task: Every morning at 9:00 AM IST
// IST is UTC + 5:30. 9:00 AM IST = 3:30 AM UTC.
cron.schedule('30 3 * * *', async () => {
    console.log('Running daily health check at 9:00 AM IST...');
    
    try {
        const connection = await mysql.createConnection(dbConfig);
        await connection.ping();
        await connection.end();
        
        // Success Email
        await transporter.sendMail({
            from: '"System Monitor" <admin@example.com>',
            to: adminEmail,
            subject: '✅ Godrej Vanantara Daily Report: Success',
            text: '✅ Godrej Vanantara Daily Report: Server and Database are working perfectly.'
        });
        console.log('Success report sent.');
    } catch (error) {
        console.error('Health check failed:', error);
        
        // Failure Email
        await transporter.sendMail({
            from: '"System Monitor" <admin@example.com>',
            to: adminEmail,
            subject: '🚨 Godrej Vanantara Daily Report: FAILURE',
            text: '🚨 Godrej Vanantara Daily Report: Server is UP but DATABASE IS DOWN. Please check!\n\nError details: ' + error.message
        });
        console.log('Failure report sent.');
    }
});

console.log('Daily Health Report Cron Service Started (Runs at 9:00 AM IST).');
