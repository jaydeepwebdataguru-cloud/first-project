import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    encrypted: true,
    authEndpoint: '/broadcasting/auth',  // for private channels
    withCredentials: true,
    auth: {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    }
});

if (window.Laravel.userId) {
  window.Echo.private(`user.${window.Laravel.userId}`)
      .listen('.MessageSent', (e) => {
          // Check if the chat with the sender is open
          const openChatUserId = window.selectedUserId;
          const senderId = e.from_user_id;
          if (parseInt(openChatUserId) === senderId) {
              const chatBox = document.getElementById('chatBox');

              const placeholder = document.getElementById('noMessagesPlaceholder');
              if (placeholder) placeholder.remove();
              
              const messageHtml = `<div class="text-left">
                  <span class="inline-block bg-blue-100 dark:bg-blue-800 text-gray-900 dark:text-white px-3 py-2 rounded mb-1">${e.message}</span>
              </div>`;
              chatBox.innerHTML += messageHtml;
              chatBox.scrollTop = chatBox.scrollHeight;
          } else {
              // Optionally notify user (badge, toast, etc.)
              console.log(`New message from user ${senderId}, but chat is not open.`);
          }
      });
}

// if (window.Laravel.userId) {
//   window.Echo.channel(`user.${window.Laravel.userId}`)
//       .listen('.MessageSent', (e) => {
//           console.log('Message received in JS:', e.message);
//       });
// }
