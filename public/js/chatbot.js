// Chatbot JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const chatbotToggler = document.getElementById('chatbot-toggler');
    const chatbotPopup = document.querySelector('.chatbot-popup');
    const closeChatbot = document.getElementById('close-chatbot');
    const chatForm = document.querySelector('.chat-form');
    const messageInput = document.getElementById('message-input');
    const chatBody = document.querySelector('.chat-body');
    const openIcon = chatbotToggler.querySelector('.open-icon');
    const closeIcon = chatbotToggler.querySelector('.close-icon');

    // Toggle chatbot popup
    chatbotToggler.addEventListener('click', function() {
        chatbotPopup.classList.toggle('show');
        chatbotToggler.classList.toggle('open');
    });

    closeChatbot.addEventListener('click', function() {
        chatbotPopup.classList.remove('show');
        chatbotToggler.classList.remove('open');
    });

    // Handle form submission
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const message = messageInput.value.trim();
        if (message) {
            addMessage('user', message);
            messageInput.value = '';

            // Simulate bot response (you can replace this with actual API call)
            setTimeout(() => {
                addMessage('bot', 'Terima kasih atas pesan Anda. Bagaimana saya bisa membantu?');
            }, 1000);
        }
    });

    // Function to add message to chat
    function addMessage(sender, text) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message`;

        const avatar = document.createElement('div');
        avatar.className = `${sender}-avatar`;
        if (sender === 'bot') {
            avatar.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 1024 1024">
                <path d="M738.3 287.6H285.7c-59 0-106.8 47.8-106.8 106.8v303.1c0 59 47.8 106.8 106.8 106.8h81.5v111.1c0 .7.8 1.1 1.4.7l166.9-110.6 41.8-.8h117.4l43.6-.4c59 0 106.8-47.8 106.8-106.8V394.5c0-59-47.8-106.9-106.8-106.9zM351.7 448.2c0-29.5 23.9-53.5 53.5-53.5s53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5-53.5-23.9-53.5-53.5zm157.9 267.1c-67.8 0-123.8-47.5-132.3-109h264.6c-8.6 61.5-64.5 109-132.3 109zm110-213.7c-29.5 0-53.5-23.9-53.5-53.5s23.9-53.5 53.5-53.5 53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5zM867.2 644.5V453.1h26.5c19.4 0 35.1 15.7 35.1 35.1v121.1c0 19.4-15.7 35.1-35.1 35.1h-26.5zM95.2 609.4V488.2c0-19.4 15.7-35.1 35.1-35.1h26.5v191.3h-26.5c-19.4 0-35.1-15.7-35.1-35.1zM561.5 149.6c0 23.4-15.6 43.3-36.9 49.7v44.9h-30v-44.9c-21.4-6.5-36.9-26.3-36.9-49.7 0-28.6 23.3-51.9 51.9-51.9s51.9 23.3 51.9 51.9z"></path>
            </svg>`;
        } else {
            avatar.innerHTML = '<span style="color: white; font-size: 20px;">U</span>';
        }

        const messageText = document.createElement('div');
        messageText.className = 'message-text';
        messageText.textContent = text;

        messageDiv.appendChild(avatar);
        messageDiv.appendChild(messageText);
        chatBody.appendChild(messageDiv);

        // Scroll to bottom
        chatBody.scrollTop = chatBody.scrollHeight;
    }
});
