<?php
// Title: Main Application Page
session_start();
// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cybersecurity Awareness Training Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-gray-100 font-sans">
    <!-- Navigation Bar -->
    <nav class="tbg-gray-800 font-sans p-6 rounded-lg">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-4xl font-bold">Cybersecurity Training</h1>
            <div>
                <span id="userProgress" class="mr-4">Progress: 0%</span>
                <button id="homePage" class="bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded mr-2">Home</button>
                <button id="logout" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded">Logout</button>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto p-6">
        <!-- Welcome Section -->
        <section id="welcome" class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-3xl font-semibold mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> 👨🏻‍💻</h2>
            <p class="text-gray-700 mb-4">Learn to protect against cyber threats. Choose a module or puzzle to begin.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <button id="startPhishing" class="bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700">Phishing Training</button>
                <button id="startPassword" class="bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700">Password Security</button>
                <button id="startSocialEng" class="bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700">Social Engineering</button>
                <button id="startPuzzle" class="bg-purple-600 text-white px-6 py-3 rounded hover:bg-purple-700">Phishing Email Puzzle</button>
            </div>
        </section>

        <!-- Phishing Module -->
        <section id="modulePhishing" class="hidden bg-white rounded-lg shadow-md p-6 mb-6 flex items-center">
            <div class="w-1/2">
                <h2 class="text-2xl font-semibold mb-4">Phishing Awareness Training</h2>
                <p class="text-gray-700 mb-4">Phishing emails trick users into sharing sensitive data. Learn to spot them.</p>
                <div>
                    <h3 class="text-xl font-medium mb-2">What is Phishing?</h3>
                    <p class="text-gray-700 mb-4">Phishing is a cyber attack where someone tries to trick you into giving away sensitive information — like passwords, credit card numbers, or personal details — by pretending to be a trustworthy person or company.</p>
                    <p class="text-gray-700 mb-4">Usually, phishing happens through:</p>
                    <p class="text-gray-700 mb-4">Emails ("Your account is locked! Click here to fix it.")</p>
                    <p class="text-gray-700 mb-4">Text messages ("You won a prize! Claim now.")</p>
                    <p class="text-gray-700 mb-4">Fake websites that look almost identical to real ones.</p>
                    <p class="text-gray-700 mb-4">The goal is to make you panic or excited, so you act quickly without thinking.</p>
                    <h3 class="text-xl font-medium mb-2">Example</h3>
                    <p class="text-gray-700 mb-4">Subject: Urgent Account Verification<br>Dear User,<br>Verify your account at [Link] to avoid suspension.<br>Regards,<br>[Fake Bank]</p>
                    <h3 class="text-xl font-medium mb-2">Attempts History</h3>
                    <p id="phishingAttempts" class="text-gray-700 mb-4">No attempts yet.</p>
                    <button id="startPhishingQuiz" class="bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700">Take Quiz</button>
                </div>
            </div>
            <div class="w-2/3 text-right ml-auto" style="margin-top: -60px; margin-left: auto; max-width: 80%;">
                <img src="https://images.squarespace-cdn.com/content/v1/5aa96c579772aea9adaa2ef7/219964b7-fb57-47df-be6a-d923c0df4d48/SLAMMethod_323.png?format=2500w" alt="Phishing Awareness" class="rounded-lg shadow-md" style="max-width: 90%; height: auto;">
            </div>
        </section>

        <!-- Password Security Module -->
        <section id="modulePassword" class="hidden bg-white rounded-lg shadow-md p-6 mb-6 flex items-center">
            <div class="w-1/2">
                <h2 class="text-2xl font-semibold mb-4">Password Security Training</h2>
                <p class="text-gray-700 mb-4">Strong passwords prevent unauthorized access. Learn to create secure ones.</p>
                <div>
                    <h3 class="text-xl font-medium mb-2">What Makes a Strong Password?</h3>
                    <p class="text-gray-700 mb-4">A strong password is one that is hard for others (or computers) to guess or crack.Here’s what makes a password strong:</p>
                    <p class="text-xl font-semibold mb-3">🔒 Key Features of a Strong Password:</p>
                    <p class="text-gray-700 mb-4">Length: At least 12 characters long (the longer, the better!).</p>
                    <p class="text-gray-700 mb-4">-Mix of Characters:</p>
                    <p class="text-gray-700 mb-4">Uppercase letters (A–Z), Lowercase letters (a–z), Numbers (0–9), Special symbols (! @ # $ % ^ & *)</p>
                    <p class="text-gray-700 mb-4">No Common Words: Avoid using obvious words like password, 123456, or your own name.</p>
                    <p class="text-gray-700 mb-4">No Personal Information: Don’t use things like your birthday, address, or pet’s name.</p>
                    <p class="text-gray-700 mb-4">Randomness: The password should be unpredictable (not just "Summer2024!").</p>
                    <h3 class="text-xl font-medium mb-2">Example</h3>
                    <p class="text-gray-700 mb-4">Weak: password123<br>Strong: X7#mZ9$kL2@pQ8</p>
                    <h3 class="text-xl font-medium mb-2">Attempts History</h3>
                    <p id="passwordAttempts" class="text-gray-700 mb-4">No attempts yet.</p>
                    <button id="startPasswordQuiz" class="bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700">Take Quiz</button>
                </div>
            </div>
            <div class="w-2/3 text-right ml-auto" style="margin-top: -60px; margin-left: auto; max-width: 80%;">
                <img src="https://www.ece.cmu.edu/_media/news-images/2016/05/2016-password-900x600.png" alt="Password Security" class="rounded-lg shadow-md" style="max-width: 90%; height: auto;">
            </div>
        </section>

        <!-- Social Engineering Module -->
        <section id="moduleSocialEng" class="hidden bg-white rounded-lg shadow-md p-6 mb-6 flex items-center">
            <div class="w-1/2">
                <h2 class="text-2xl font-semibold mb-4">Social Engineering Training</h2>
                <p class="text-gray-700 mb-4">Social engineering manipulates users into sharing sensitive data. Learn to recognize it.</p>
                <div>
                    <h3 class="text-xl font-medium mb-2">What is Social Engineering?</h3>
                    <p class="text-gray-700 mb-4">Social engineering is when someone manipulates people into giving away confidential information — like passwords, bank details, or access to systems — by tricking or influencing them, rather than hacking computers directly. In simple terms:</p>
                    <p class="text-gray-700 mb-4">🔎 It's hacking people, not computers.</p>
                    <p class="text-xl font-semibold mb-3">🔒 Common social engineering tricks:</p>
                    <p class="text-gray-700 mb-4">Phishing emails ("Click this fake link to fix your account.")</p>
                    <p class="text-gray-700 mb-4">Phone scams ("I'm from tech support. Give me your password to fix your computer.")</p>
                    <p class="text-gray-700 mb-4">Fake emergencies ("Your boss needs you to urgently transfer money!")</p>
                    <p class="text-gray-700 mb-4">Pretending to be someone you trust (like a coworker, bank agent, or IT staff).</p>
                    <p class="text-gray-700 mb-4"></p>
                    <h3 class="text-xl font-medium mb-2">Example</h3>
                    <p class="text-gray-700 mb-4">A caller posing as IT asks for your login credentials to "fix a system issue."</p>
                    <h3 class="text-xl font-medium mb-2">Attempts History</h3>
                    <p id="socialEngAttempts" class="text-gray-700 mb-4">No attempts yet.</p>
                    <button id="startSocialEngQuiz" class="bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700">Take Quiz</button>
                </div>
            </div>
            <div class="w-2/3 text-right ml-auto" style="margin-top: -60px; margin-left: auto; max-width: 80%;">
                <img src="https://sp-ao.shortpixel.ai/client/to_webp,q_glossy,ret_img,w_1000/https://www.osibeyond.com/wp-content/uploads/2022/12/social-engineering-graphic.png" alt="Social Engineering" class="rounded-lg shadow-md" style="max-width: 90%; height: auto;">
            </div>
        </section>

        <!-- Puzzle Section -->
        <section id="puzzleSection" class="hidden bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-semibold mb-4">Phishing Email Sorting Puzzle</h2>
            <p class="text-gray-700 mb-4">Drag emails into the correct category: Phishing or Legitimate.</p>
            <div class="flex justify-between mb-4">
                <div id="phishingBox" class="w-1/2 p-4 bg-red-100 rounded-lg min-h-[200px]">
                    <h3 class="text-lg font-medium">Phishing</h3>
                </div>
                <div id="legitBox" class="w-1/2 p-4 bg-green-100 rounded-lg min-h-[200px] ml-4">
                    <h3 class="text-lg font-medium">Legitimate</h3>
                </div>
            </div>
            <div id="emailList" class="mb-4"></div>
            <button id="submitPuzzle" class="bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700">Submit Answers</button>
            <p id="puzzleResult" class="text-gray-700 mt-4 hidden"></p>
            <button id="finishPuzzle" class="hidden bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700 mt-4">Finish Puzzle</button>
        </section>

        <!-- Quiz Section -->
        <section id="quizSection" class="hidden bg-white rounded-lg shadow-md p-6 relative">
            <h2 id="quizTitle" class="text-2xl font-semibold mb-4"></h2>
            <p id="quizProgress" class="text-gray-600 mb-4">Question 1 of 10</p>
            <pre id="quizQuestion" class="text-gray-1000 font-semibold mb-4 bg-gray-50 p-4 rounded"></pre>
            <div id="quizOptions" class="mb-4"></div>
            <p id="quizResult" class="text-gray-700 mt-4 hidden"></p>
            <div id="quizButtons" class="flex gap-4 mt-4 flex-wrap">
                <button id="nextQuestion" class="hidden bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700">Next Question</button>
                <button id="finishQuiz" class="hidden bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700">Finish Quiz</button>
                <button id="backToModule" class="bg-gray-600 text-white px-6 py-3 rounded hover:bg-gray-700 absolute top-4 right-4 hidden">Back to Module</button>
            </div>
        </section>
    </div>

    <!-- Documentation Section -->
    <div class="container mx-auto p-6">
        <section class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-semibold mb-4">Documentation and User Guide</h2>
            <h3 class="text-xl font-medium mb-2">Overview</h3>
            <p class="text-gray-700 mb-4">
            Cybersecurity threats are constantly evolving, posing significant risks to organizations. Many employees lack sufficient training to recognize and respond to these threats, making organizations vulnerable to attacks. This project aims to develop a Cybersecurity Awareness Training Platform that provides engaging and interactive training modules for employees. For instance, an employee might unknowingly click on a phishing email, compromising the organization's security. By training employees to identify such threats, the platform can help mitigate these risks.            </p>
            <p class="text-gray-700 mb-4">
                This platform trains employees on cybersecurity through three modules (Phishing, Password Security, Social Engineering) with 10-question quizzes and a phishing email sorting puzzle with 6 emails.
            </p>
            <h3 class="text-xl font-medium mb-2">Developers</h3>
            <p class="text-gray-700 mb-4">
            
                - Abhishek<br>


            <p class="text-gray-700 mb-4">
                All Rights Reserved to Melbourne Institute of Technology
            </p>


    <script src="scripts.js"></script>
</body>
</html>