<x-app-layout>
    <div class="flex h-[calc(100vh-64px)] overflow-hidden">
        <!-- Sidebar -->
        <div class="w-1/4 bg-white dark:bg-gray-900 border-r overflow-y-auto">
            <div class="p-4 text-gray-800 dark:text-white text-lg font-semibold">Chats</div>
            <ul id="userList">
                @foreach ($users as $user)
                    <li data-user-id="{{ $user->id }}" class="cursor-pointer px-4 py-2 hover:bg-blue-100 dark:hover:bg-blue-700 user-item">
                        {{ $user->name }}
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="w-3/4 flex flex-col bg-gradient-to-br from-blue-50 to-white">
            <div id="chatBox" class="h-full overflow-y-auto p-4 space-y-2 bg-white dark:bg-gray-800 rounded-lg shadow-inner">
                <!-- Messages will be appended here -->
            </div>

            <!-- Default Placeholder Background (shown only when no chat selected) -->
            <div id="chatPlaceholder" class="my-auto justify-center items-center dark:from-gray-800 dark:to-gray-900 rounded-lg">
                <div class="text-center flex flex-col items-center">
                    <img src="https://cdn-icons-png.flaticon.com/512/4712/4712100.png" alt="Chat illustration" class="w-28 h-28 opacity-70 mb-6">
                    <h2 class="text-xl font-semibold text-gray-600 dark:text-gray-300">Start a Conversation</h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Select a user to begin chatting.</p>
                </div>
            </div>
                
            <div class="p-4 border-t dark:border-gray-700 bg-white dark:bg-gray-900">
                <input type="hidden" id="to_user_id">
                <div class="flex space-x-2">
                    <input 
                        type="text" 
                        id="message_input" 
                        placeholder="Type your message..." 
                        class="flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    <button 
                        id="sendBtn" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-300"
                    >
                        Send
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Setup JS -->
    <script>

        let selectedUserId = null;
        document.getElementById('chatBox').style.display = 'none';
        document.getElementById('chatPlaceholder').style.display = 'block';

        // Highlight and load messages
        document.querySelectorAll('.user-item').forEach(item => {
            item.addEventListener('click', function () {
                document.querySelectorAll('.user-item').forEach(i => i.classList.remove('bg-gray-200', 'dark:bg-gray-600'));
                this.classList.add('bg-gray-200', 'dark:bg-gray-600');

                const userId = this.getAttribute('data-user-id');
                window.selectedUserId = userId;
                document.getElementById('to_user_id').value = userId;
                // Show chatBox, hide placeholder
                document.getElementById('chatBox').style.display = 'block';
                document.getElementById('chatPlaceholder').style.display = 'none';
                fetchMessages(userId);
            });
        });

        function fetchMessages(userId) {
            fetch(`/fetch-messages/${userId}`)
                .then(response => response.json())
                .then(data => {
                    const chatBox = document.getElementById('chatBox');
                    chatBox.innerHTML = '';

                    if (data.length === 0) {
                        chatBox.innerHTML = `
                            <div id="noMessagesPlaceholder" class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400 text-lg">
                                No messages yet. Say hello!
                            </div>
                        `;
                        return;
                    }

                    data.forEach(msg => {
                        const alignClass = msg.from_user_id == {{ auth()->id() }} ? 'text-right' : 'text-left';
                        chatBox.innerHTML += `
                            <div class="${alignClass}">
                                <span class="inline-block bg-blue-100 dark:bg-blue-800 text-gray-900 dark:text-white px-3 py-2 rounded mb-1">
                                    ${msg.message}
                                </span>
                            </div>
                        `;
                    });

                    chatBox.scrollTop = chatBox.scrollHeight;
                });
        }

        // Send message
        const messageInput = document.getElementById('message_input');
        const sendBtn = document.getElementById('sendBtn');

        function sendMessage(){
            const toUserId = window.selectedUserId || document.getElementById('to_user_id').value;
            const message = messageInput.value;

            if (!toUserId || !message.trim()) {
                alert('Select a user and enter a message.');
                return;
            }

            const chatBox = document.getElementById('chatBox');
            const placeholder = document.getElementById('noMessagesPlaceholder');
            if (placeholder) placeholder.remove();

            const messageHtml = `<div class="text-right">
                <span class="inline-block bg-blue-100 dark:bg-blue-800 text-gray-900 dark:text-white px-3 py-2 rounded mb-1">${message}</span>
            </div>`;
            chatBox.innerHTML += messageHtml;
            chatBox.scrollTop = chatBox.scrollHeight;
            messageInput.value = ''; 

            fetch('/send-message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ message, to_user_id: toUserId })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Response:', data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Send on button click
        sendBtn.addEventListener('click', sendMessage);
        // Send on Enter key press
        messageInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault(); // Prevent newline
                sendMessage();
            }
        });

    </script>
</x-app-layout>

