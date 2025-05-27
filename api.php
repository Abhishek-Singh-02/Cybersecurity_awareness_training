<?php
// Title: Backend API
require 'db.php';
session_start();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'get_quiz') {
    // Fetch all 10 questions for the module, randomized
    $module = $_GET['module'] ?? '';
    try {
        $stmt = $pdo->prepare('SELECT * FROM quizzes WHERE module = ? ORDER BY RAND()');
        $stmt->execute([$module]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Fetched questions for module $module: " . count($questions));
        echo json_encode($questions);
    } catch (PDOException $e) {
        error_log("Error in get_quiz: " . $e->getMessage());
        echo json_encode(['error' => 'Failed to fetch questions']);
    }
} elseif ($action === 'submit_quiz') {
    // Submit quiz answer, update progress if correct, and save attempt
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['quiz_id'], $data['answer'], $data['module'])) {
        error_log("Invalid submit_quiz data: " . json_encode($data));
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    if (!isset($_SESSION['user_id'])) {
        error_log("Session user_id not set in submit_quiz");
        echo json_encode(['error' => 'User not authenticated']);
        return;
    }

    $quiz_id = $data['quiz_id'];
    $answer = $data['answer'];
    $user_id = $_SESSION['user_id'];
    $score = $data['score'] ?? null;
    $module = $data['module'];

    try {
        // Validate quiz_id
        $stmt = $pdo->prepare('SELECT correct_option FROM quizzes WHERE id = ?');
        $stmt->execute([$quiz_id]);
        $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$quiz) {
            error_log("Quiz ID $quiz_id not found for module $module, user $user_id");
            echo json_encode(['error' => "Quiz ID $quiz_id not found"]);
            return;
        }

        $correct = $answer == $quiz['correct_option']; // Use == for type coercion
        if ($correct) {
            $stmt = $pdo->prepare('INSERT INTO user_progress (user_id, module, question_id, completed) VALUES (?, ?, ?, TRUE) ON DUPLICATE KEY UPDATE completed = TRUE');
            $stmt->execute([$user_id, $module, $quiz_id]);
            error_log("Updated user_progress for user $user_id, module $module, quiz_id $quiz_id");
        }
        if ($score !== null) {
            $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM attempts WHERE user_id = ? AND module = ?');
            $stmt->execute([$user_id, $module]);
            $attempt_number = $stmt->fetch()['count'] + 1;
            $stmt = $pdo->prepare('INSERT INTO attempts (user_id, module, attempt_number, score) VALUES (?, ?, ?, ?)');
            $stmt->execute([$user_id, $module, $attempt_number, $score]);
            error_log("Saved attempt for user $user_id, module $module, attempt $attempt_number, score $score");
        }
        echo json_encode(['correct' => $correct]);
    } catch (PDOException $e) {
        error_log("Error in submit_quiz for quiz_id $quiz_id, user $user_id: " . $e->getMessage());
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'get_puzzle') {
    // Fetch puzzle data
    try {
        $stmt = $pdo->prepare('SELECT * FROM puzzles WHERE id = 1');
        $stmt->execute();
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        error_log("Error in get_puzzle: " . $e->getMessage());
        echo json_encode(['error' => 'Failed to fetch puzzle']);
    }
} elseif ($action === 'submit_puzzle') {
    // Submit puzzle answers
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['puzzle_id'], $data['answers'])) {
        error_log("Invalid submit_puzzle data: " . json_encode($data));
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }

    $puzzle_id = $data['puzzle_id'];
    $answers = $data['answers'];
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare('SELECT correct_answers FROM puzzles WHERE id = ?');
        $stmt->execute([$puzzle_id]);
        $puzzle = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$puzzle) {
            error_log("Puzzle ID $puzzle_id not found");
            echo json_encode(['error' => "Puzzle ID $puzzle_id not found"]);
            return;
        }

        $correct_answers = json_decode($puzzle['correct_answers'], true);
        $correct = true;
        foreach ($correct_answers as $email_id => $type) {
            if ($answers[$email_id] !== $type) {
                $correct = false;
                break;
            }
        }
        echo json_encode(['correct' => $correct]);
    } catch (PDOException $e) {
        error_log("Error in submit_puzzle: " . $e->getMessage());
        echo json_encode(['error' => 'Database error']);
    }
} elseif ($action === 'get_progress') {
    // Calculate progress based on correct quiz answers (30 questions)
    try {
        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare('SELECT COUNT(*) as correct FROM user_progress WHERE user_id = ? AND completed = TRUE AND module != "puzzle"');
        $stmt->execute([$user_id]);
        $correct = $stmt->fetch()['correct'];
        $progress = ($correct / 30) * 100; // 30 quiz questions
        error_log("Progress for user $user_id: $progress% ($correct/30)");
        echo json_encode(['progress' => $progress]);
    } catch (PDOException $e) {
        error_log("Error in get_progress: " . $e->getMessage());
        echo json_encode(['error' => 'Failed to fetch progress']);
    }
} elseif ($action === 'reset_module') {
    // Reset progress and attempts for a specific module
    $module = $_GET['module'] ?? '';
    $user_id = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare('DELETE FROM user_progress WHERE user_id = ? AND module = ?');
        $stmt->execute([$user_id, $module]);
        $stmt = $pdo->prepare('DELETE FROM attempts WHERE user_id = ? AND module = ?');
        $stmt->execute([$user_id, $module]);
        error_log("Reset module $module for user $user_id");
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        error_log("Error in reset_module: " . $e->getMessage());
        echo json_encode(['error' => 'Failed to reset module']);
    }
} elseif ($action === 'reset_puzzle') {
    // Reset puzzle progress
    $user_id = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare('DELETE FROM user_progress WHERE user_id = ? AND module = "puzzle"');
        $stmt->execute([$user_id]);
        error_log("Reset puzzle for user $user_id");
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        error_log("Error in reset_puzzle: " . $e->getMessage());
        echo json_encode(['error' => 'Failed to reset puzzle']);
    }
} elseif ($action === 'get_attempts') {
    // Fetch up to 5 most recent attempts for a module
    $module = $_GET['module'] ?? '';
    $user_id = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare('SELECT attempt_number, score FROM attempts WHERE user_id = ? AND module = ? ORDER BY attempt_number DESC LIMIT 5');
        $stmt->execute([$user_id, $module]);
        $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($attempts)) {
            error_log("No attempts found for user $user_id, module $module");
            echo json_encode([]); // Return empty array if no attempts
        } else {
            error_log("Fetched attempts for user $user_id, module $module: " . json_encode($attempts));
            echo json_encode($attempts);
        }
    } catch (PDOException $e) {
        error_log("Error in get_attempts for user $user_id, module $module: " . $e->getMessage());
        echo json_encode(['error' => 'Failed to fetch attempts']);
    }
}
?>