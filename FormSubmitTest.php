<?php

use PHPUnit\Framework\TestCase;

class FormSubmitTest extends TestCase
{
    protected $db;
    protected $stmt;

    public function testFormSubmission()
    {
    $mockPdo = $this->createMock(PDO::class);
    $mockStmt = $this->createMock(PDOStatement::class);

    $mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($mockStmt);

    $mockStmt->expects($this->once())
             ->method('execute')
             ->willReturn(true);

    global $db;
    $db = $mockPdo;

    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [
        'contact' => 'email',
        'firstname' => 'John Doe',
        'phone' => '1234567890',
        'email1' => 'johndoe@example.com',
        'date' => '2025-03-18',
        'description' => 'Test message'
    ];

    ob_start();
    include 'contactHandler.php';
    ob_end_clean();
}
public function testSuccessfulSubmission()
    {
        $_POST = [
            'contact' => 'email',
            'email1' => 'test@example.com',
            'email2' => 'test@example.com',
            'phone' => '1234567890',
            'firstname' => 'John Doe',
            'date' => '2025-03-18',
            'description' => 'Test message',
        ];

        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);
        $pdo->method('prepare')->willReturn($stmt);
        $stmt->method('execute')->willReturn(true);

        $GLOBALS['db'] = $pdo;

        ob_start();
        include 'contactHandler.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Contact sent successfully', $output);
    }
    public function testDatabaseError()
    {
        $_POST = [
            'contact' => 'email',
            'email1' => 'test@example.com',
            'phone' => '1234567890',
            'firstname' => 'John Doe',
            'date' => '2025-03-18',
            'description' => 'Test message',
        ];
    
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->will($this->throwException(new PDOException('Database connection error')));
    
        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);
    
        global $db;
        $db = $pdo;
    
        ob_start();
        include 'contactHandler.php';
        $output = ob_get_clean();
    
        file_put_contents('debug_output.txt', $output);
    
        $this->assertStringContainsString('Error inserting record', $output);
    }
    
    
    
    

}
