// Title: Frontend JavaScript Logic

// Initialize DOM elements
const sections = {
    welcome: document.getElementById('welcome'),
    modulePhishing: document.getElementById('modulePhishing'),
    modulePassword: document.getElementById('modulePassword'),
    moduleSocialEng: document.getElementById('moduleSocialEng'),
    puzzleSection: document.getElementById('puzzleSection'),
    quizSection: document.getElementById('quizSection')
};

const buttons = {
    startPhishing: document.getElementById('startPhishing'),
    startPassword: document.getElementById('startPassword'),
    startSocialEng: document.getElementById('startSocialEng'),
    startPuzzle: document.getElementById('startPuzzle'),
    startPhishingQuiz: document.getElementById('startPhishingQuiz'),
    startPasswordQuiz: document.getElementById('startPasswordQuiz'),
    startSocialEngQuiz: document.getElementById('startSocialEngQuiz'),
    submitPuzzle: document.getElementById('submitPuzzle'),
    finishPuzzle: document.getElementById('finishPuzzle'),
    finishQuiz: document.getElementById('finishQuiz'),
    backToModule: document.getElementById('backToModule'),
    nextQuestion: document.getElementById('nextQuestion'),
    homePage: document.getElementById('homePage'),
    logout: document.getElementById('logout')
};

const elements = {
    userProgress: document.getElementById('userProgress'),
    quizTitle: document.getElementById('quizTitle'),
    quizProgress: document.getElementById('quizProgress'),
    quizQuestion: document.getElementById('quizQuestion'),
    quizOptions: document.getElementById('quizOptions'),
    quizResult: document.getElementById('quizResult'),
    emailList: document.getElementById('emailList'),
    phishingBox: document.getElementById('phishingBox'),
    legitBox: document.getElementById('legitBox'),
    puzzleResult: document.getElementById('puzzleResult'),
    phishingAttempts: document.getElementById('phishingAttempts'),
    passwordAttempts: document.getElementById('passwordAttempts'),
    socialEngAttempts: document.getElementById('socialEngAttempts')
};

let currentModule = ''; // Track current module
let quizQuestions = []; // Store 10 questions
let currentQuestionIndex = 0; // Track current question
let score = 0; // Track correct answers
let answeredQuestions = new Set(); // Track answered question IDs

// Show/hide sections
function showSection(sectionId) {
    Object.values(sections).forEach(section => section.classList.add('hidden'));
    sections[sectionId].classList.remove('hidden');
}

// Update user progress
async function updateProgress() {
    try {
        const response = await fetch('api.php?action=get_progress');
        const data = await response.json();
        if (data.error) {
            console.error('Progress error:', data.error);
            elements.userProgress.textContent = 'Progress: Error';
            return;
        }
        elements.userProgress.textContent = `Progress: ${data.progress.toFixed(2)}%`;
    } catch (error) {
        console.error('Error updating progress:', error);
        elements.userProgress.textContent = 'Progress: Error';
    }
}

// Fetch and display attempt history (up to 5)
async function updateAttempts(module, element) {
    try {
        const response = await fetch(`api.php?action=get_attempts&module=${module}`);
        const attempts = await response.json();
        console.log(`Attempts fetched for ${module}:`, attempts); // Debug log
        if (attempts.error) {
            console.error('Attempts error:', attempts.error);
            element.textContent = 'Error loading attempts.';
            return;
        }
        // Ensure attempts is an array, even if empty
        const attemptList = Array.isArray(attempts) ? attempts : [];
        if (attemptList.length === 0) {
            element.textContent = 'No attempts yet.';
        } else {
            // Sort by attempt_number descending to ensure newest first
            attemptList.sort((a, b) => b.attempt_number - a.attempt_number);
            // Map attempts to display string, labeling the first as "Last Attempt"
            const attemptText = attemptList
                .map((a, index) => 
                    index === 0 
                        ? `Last Attempt: ${a.score}/10`
                        : `Attempt ${a.attempt_number}: ${a.score}/10`
                )
                .join(', ');
            element.textContent = attemptText;
            console.log(`Rendered attempts for ${module}: ${attemptText}`); // Debug log
        }
    } catch (error) {
        console.error('Error fetching attempts:', error);
        element.textContent = 'Error loading attempts.';
    }
}

// Reset module data
async function resetModule(module) {
    try {
        const response = await fetch(`api.php?action=reset_module&module=${module}`);
        const data = await response.json();
        if (data.error) {
            console.error('Reset module error:', data.error);
            return;
        }
        score = 0;
        answeredQuestions.clear();
        quizQuestions = []; // Clear questions to force re-fetch
        await updateProgress();
        await updateAttempts(module, elements[`${module}Attempts`]);
    } catch (error) {
        console.error('Error resetting module:', error);
    }
}

// Reset puzzle data
async function resetPuzzle() {
    try {
        const response = await fetch('api.php?action=reset_puzzle');
        const data = await response.json();
        if (data.error) {
            console.error('Reset puzzle error:', data.error);
            return;
        }
        await updateProgress();
    } catch (error) {
        console.error('Error resetting puzzle:', error);
    }
}

// Load quiz for a module
async function loadQuiz(module) {
    currentModule = module;
    await resetModule(module); // Reset on quiz start
    currentQuestionIndex = 0;
    try {
        const response = await fetch(`api.php?action=get_quiz&module=${module}`);
        quizQuestions = await response.json();
        console.log(`Loaded questions for ${module}:`, quizQuestions); // Debug log
        if (quizQuestions.error) {
            console.error('Quiz error:', quizQuestions.error);
            elements.quizResult.textContent = 'Error loading quiz: ' + quizQuestions.error;
            return;
        }
        if (quizQuestions.length < 10) {
            console.error('Not enough questions:', quizQuestions.length);
            elements.quizResult.textContent = 'Error: Not enough questions available.';
            return;
        }
        loadQuestion();
        showSection('quizSection');
    } catch (error) {
        console.error('Error loading quiz:', error);
        elements.quizResult.textContent = 'Error loading quiz.';
    }
}

// Load current question
function loadQuestion() {
    if (currentQuestionIndex >= quizQuestions.length) {
        console.error('Invalid question index:', currentQuestionIndex);
        elements.quizResult.textContent = 'Error: No more questions.';
        return;
    }
    const quiz = quizQuestions[currentQuestionIndex];
    elements.quizTitle.textContent = `${currentModule.charAt(0).toUpperCase() + currentModule.slice(1)} Quiz`;
    elements.quizProgress.textContent = `Question ${currentQuestionIndex + 1} of 10`;
    elements.quizQuestion.textContent = quiz.question;
    elements.quizOptions.innerHTML = '';
    try {
        const options = JSON.parse(quiz.options);
        options.forEach((option, index) => {
            const btn = document.createElement('button');
            btn.className = 'bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mr-2 mb-2';
            btn.textContent = option;
            btn.disabled = answeredQuestions.has(quiz.id);
            btn.addEventListener('click', () => submitQuiz(quiz.id, index));
            elements.quizOptions.appendChild(btn);
        });
    } catch (error) {
        console.error('Error parsing options:', error);
        elements.quizResult.textContent = 'Error loading options.';
        return;
    }
    elements.quizResult.classList.add('hidden');
    buttons.nextQuestion.classList.add('hidden');
    buttons.finishQuiz.classList.add('hidden');
    buttons.backToModule.classList.remove('hidden');
    buttons.backToModule.classList.toggle('phishing', currentModule === 'phishing');
}

// Submit quiz answer
async function submitQuiz(quizId, answer) {
    if (answeredQuestions.has(quizId)) {
        console.log(`Question ${quizId} already answered`);
        return; // Prevent re-answering
    }
    answeredQuestions.add(quizId);
    try {
        console.log(`Submitting quiz_id ${quizId}, answer ${answer}, module ${currentModule}`); // Debug log
        const response = await fetch('api.php?action=submit_quiz', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                quiz_id: quizId,
                answer,
                module: currentModule,
                score: currentQuestionIndex === 9 ? score + (answer === quizQuestions[currentQuestionIndex].correct_option ? 1 : 0) : null
            })
        });
        const result = await response.json();
        console.log(`Submit quiz ${quizId} result:`, result); // Debug log
        if (result.error) {
            console.error('Submit quiz error:', result.error);
            elements.quizResult.textContent = `Error: ${result.error}`;
            elements.quizResult.classList.remove('hidden');
            return;
        }
        elements.quizResult.textContent = result.correct
            ? 'Correct!'
            : 'Incorrect.';
        elements.quizResult.classList.remove('hidden');
        if (result.correct) score++;

        // Disable all option buttons
        Array.from(elements.quizOptions.children).forEach(btn => btn.disabled = true);

        if (currentQuestionIndex < 9) {
            buttons.nextQuestion.classList.remove('hidden');
        } else {
            elements.quizResult.textContent = `Quiz completed! Score: ${score}/10`;
            buttons.finishQuiz.classList.remove('hidden');
            await updateAttempts(currentModule, elements[`${currentModule}Attempts`]);
        }
        await updateProgress();
    } catch (error) {
        console.error('Error submitting quiz:', error);
        elements.quizResult.textContent = 'Error submitting answer: Network or server issue.';
        elements.quizResult.classList.remove('hidden');
    }
}

// Load next question
function loadNextQuestion() {
    currentQuestionIndex++;
    loadQuestion();
}

// Shuffle array for puzzle emails
function shuffle(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    return array;
}

// Load puzzle
async function loadPuzzle() {
    await resetPuzzle(); // Reset progress on puzzle start
    try {
        const response = await fetch('api.php?action=get_puzzle');
        const puzzle = await response.json();
        if (puzzle.error) {
            console.error('Puzzle error:', puzzle.error);
            elements.puzzleResult.textContent = 'Error loading puzzle: ' + puzzle.error;
            return;
        }
        // Clear previous email placements
        elements.emailList.innerHTML = '';
        elements.phishingBox.innerHTML = '<h3 class="text-lg font-medium">Phishing</h3>';
        elements.legitBox.innerHTML = '<h3 class="text-lg font-medium">Legitimate</h3>';
        // Shuffle emails
        const emails = shuffle(JSON.parse(puzzle.emails));
        // Load emails into unsorted list
        emails.forEach(email => {
            const div = document.createElement('div');
            div.className = 'p-2 bg-gray-200 rounded mb-2 cursor-move';
            div.draggable = true;
            div.dataset.emailId = email.id;
            div.textContent = email.text;
            div.addEventListener('dragstart', e => e.dataTransfer.setData('text', email.id));
            elements.emailList.appendChild(div);
        });
        showSection('puzzleSection');
        elements.puzzleResult.classList.add('hidden');
        buttons.finishPuzzle.classList.add('hidden');
        setupDragAndDrop(puzzle.id);
    } catch (error) {
        console.error('Error loading puzzle:', error);
        elements.puzzleResult.textContent = 'Error loading puzzle.';
    }
}

// Setup drag-and-drop for puzzle
function setupDragAndDrop(puzzleId) {
    const answers = {};
    [elements.phishingBox, elements.legitBox].forEach(box => {
        box.addEventListener('dragover', e => e.preventDefault());
        box.addEventListener('drop', e => {
            e.preventDefault();
            const emailId = e.dataTransfer.getData('text');
            const emailDiv = elements.emailList.querySelector(`[data-email-id="${emailId}"]`) || 
                            elements.phishingBox.querySelector(`[data-email-id="${emailId}"]`) || 
                            elements.legitBox.querySelector(`[data-email-id="${emailId}"]`);
            if (emailDiv) {
                box.appendChild(emailDiv);
                answers[emailId] = box === elements.phishingBox ? 'phishing' : 'legitimate';
            }
        });
    });
    buttons.submitPuzzle.onclick = async () => {
        try {
            const response = await fetch('api.php?action=submit_puzzle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ puzzle_id: puzzleId, answers })
            });
            const result = await response.json();
            if (result.error) {
                console.error('Puzzle submit error:', result.error);
                elements.puzzleResult.textContent = 'Error: ' + result.error;
                return;
            }
            elements.puzzleResult.textContent = result.correct
                ? 'Correct! All emails sorted correctly.'
                : 'Incorrect. Review and try again.';
            elements.puzzleResult.classList.remove('hidden');
            if (result.correct) {
                buttons.finishPuzzle.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error submitting puzzle:', error);
            elements.puzzleResult.textContent = 'Error submitting puzzle.';
        }
    };
}

// Event Listeners
buttons.startPhishing.addEventListener('click', () => {
    showSection('modulePhishing');
    updateAttempts('phishing', elements.phishingAttempts);
});
buttons.startPassword.addEventListener('click', () => {
    showSection('modulePassword');
    updateAttempts('password', elements.passwordAttempts);
});
buttons.startSocialEng.addEventListener('click', () => {
    showSection('moduleSocialEng');
    updateAttempts('socialEng', elements.socialEngAttempts);
});
buttons.startPuzzle.addEventListener('click', loadPuzzle);
buttons.startPhishingQuiz.addEventListener('click', () => loadQuiz('phishing'));
buttons.startPasswordQuiz.addEventListener('click', () => loadQuiz('password'));
buttons.startSocialEngQuiz.addEventListener('click', () => loadQuiz('socialEng'));
buttons.finishQuiz.addEventListener('click', () => showSection('welcome'));
buttons.finishPuzzle.addEventListener('click', () => showSection('welcome'));
buttons.backToModule.addEventListener('click', () => {
    const sectionMap = {
        'phishing': 'modulePhishing',
        'password': 'modulePassword',
        'socialEng': 'moduleSocialEng'
    };
    showSection(sectionMap[currentModule] || 'welcome');
    updateAttempts(currentModule, elements[`${currentModule}Attempts`]);
});
buttons.nextQuestion.addEventListener('click', loadNextQuestion);
buttons.homePage.addEventListener('click', () => showSection('welcome'));
buttons.logout.addEventListener('click', () => location.href = 'logout.php');

// Initialize progress and attempts
updateProgress();
updateAttempts('phishing', elements.phishingAttempts);
updateAttempts('password', elements.passwordAttempts);
updateAttempts('socialEng', elements.socialEngAttempts);