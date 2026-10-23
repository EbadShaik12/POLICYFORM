/**
 * PolicyFlow — Maya AI Voice & Chatbot Assistant
 * Voice Recognition (Speech-to-Text) & Voice Synthesis (Text-to-Speech)
 */

function initMayaChat() {
  // Elements
  const chatWidget = document.getElementById('chatWidget');
  const chatLauncherBtn = document.getElementById('chatLauncherBtn');
  const chatCloseBtn = document.getElementById('chatCloseBtn');
  const chatMessages = document.getElementById('chatMessages');
  const chatInputForm = document.getElementById('chatInputForm');
  const chatInput = document.getElementById('chatInput');
  const btnVoiceMic = document.getElementById('btnVoiceMic');
  const btnToggleVoiceMute = document.getElementById('btnToggleVoiceMute');
  const voiceListeningBanner = document.getElementById('voiceListeningBanner');
  const typingIndicator = document.getElementById('typingIndicator');
  const launcherTooltip = document.querySelector('.chat-launcher-tooltip');

  // State
  let isChatOpen = false;
  let isVoiceEnabled = true; // Bot speaks by default
  let isListening = false;
  let conversationHistory = [];
  let recognition = null;
  let synth = window.speechSynthesis || null;

  // Initialize Speech Recognition if supported
  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (SpeechRecognition) {
    recognition = new SpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = 'en-US';

    recognition.onstart = () => {
      isListening = true;
      btnVoiceMic.classList.add('listening');
      if (voiceListeningBanner) voiceListeningBanner.classList.add('show');
    };

    recognition.onresult = (event) => {
      const transcript = event.results[0][0].transcript;
      if (transcript && transcript.trim()) {
        sendMessage(transcript.trim(), true);
      }
    };

    recognition.onerror = (event) => {
      console.warn('Speech recognition error:', event.error);
      stopListening();
      if (event.error === 'not-allowed') {
        appendBotMessage("Microphone permission was denied. You can still type your questions to me!");
      }
    };

    recognition.onend = () => {
      stopListening();
    };
  } else {
    if (btnVoiceMic) {
      btnVoiceMic.title = 'Speech recognition not supported in this browser';
    }
  }

  function startListening() {
    if (!recognition) {
      appendBotMessage("Your browser doesn't support direct microphone input, but you can type your questions right here!");
      return;
    }
    try {
      if (synth && synth.speaking) {
        synth.cancel(); // Stop talking before listening
      }
      recognition.start();
    } catch (e) {
      console.warn('Recognition start exception:', e);
      stopListening();
    }
  }

  function stopListening() {
    isListening = false;
    if (btnVoiceMic) btnVoiceMic.classList.remove('listening');
    if (voiceListeningBanner) voiceListeningBanner.classList.remove('show');
    try {
      if (recognition) recognition.stop();
    } catch (e) {}
  }

  // Voice synthesis / Human Speech
  function speakText(text) {
    if (!isVoiceEnabled || !synth) return;

    try {
      synth.cancel(); // Cancel any ongoing speech
      const cleanText = text.replace(/[*_#`•]/g, ' ').replace(/\s+/g, ' ').trim();
      const utterance = new SpeechSynthesisUtterance(cleanText);

      // Select natural sounding female voice if available
      const voices = synth.getVoices();
      const preferredVoice = voices.find(v => 
        (v.name.includes('Google') || v.name.includes('Samantha') || v.name.includes('Zira') || v.name.includes('Natural') || v.name.includes('Victoria')) && v.lang.startsWith('en')
      ) || voices.find(v => v.lang.startsWith('en')) || null;

      if (preferredVoice) {
        utterance.voice = preferredVoice;
      }
      utterance.pitch = 1.05; // Slightly warmer pitch
      utterance.rate = 0.98;  // Natural conversational pace
      synth.speak(utterance);
    } catch (e) {
      console.warn('Speech synthesis error:', e);
    }
  }

  // Pre-load voices
  if (synth && synth.onvoiceschanged !== undefined) {
    synth.onvoiceschanged = () => synth.getVoices();
  }

  // Toggle voice mute
  if (btnToggleVoiceMute) {
    btnToggleVoiceMute.addEventListener('click', () => {
      isVoiceEnabled = !isVoiceEnabled;
      if (isVoiceEnabled) {
        btnToggleVoiceMute.classList.add('active-voice');
        btnToggleVoiceMute.innerHTML = `
          <svg style="width:16px; height:16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
            <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
          </svg>
        `;
        btnToggleVoiceMute.title = "Voice assistant speaking: ON";
      } else {
        btnToggleVoiceMute.classList.remove('active-voice');
        btnToggleVoiceMute.innerHTML = `
          <svg style="width:16px; height:16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
            <line x1="23" y1="9" x2="17" y2="15"></line>
            <line x1="17" y1="9" x2="23" y2="15"></line>
          </svg>
        `;
        btnToggleVoiceMute.title = "Voice assistant speaking: MUTED";
        if (synth) synth.cancel();
      }
    });
  }

  // Toggle Chatbot
  function toggleChat() {
    isChatOpen = !isChatOpen;
    if (isChatOpen) {
      chatWidget.classList.add('open');
      if (launcherTooltip) launcherTooltip.style.display = 'none';
      if (chatInput) chatInput.focus();
    } else {
      chatWidget.classList.remove('open');
      stopListening();
      if (synth) synth.cancel();
    }
  }

  if (chatLauncherBtn) chatLauncherBtn.addEventListener('click', toggleChat);
  if (chatCloseBtn) chatCloseBtn.addEventListener('click', toggleChat);

  // Microphone click
  if (btnVoiceMic) {
    btnVoiceMic.addEventListener('click', () => {
      if (isListening) {
        stopListening();
      } else {
        startListening();
      }
    });
  }

  // Form Submit
  if (chatInputForm) {
    chatInputForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const message = chatInput.value.trim();
      if (!message) return;
      chatInput.value = '';
      sendMessage(message, false);
    });
  }

  // Send message to server
  async function sendMessage(text, wasSpoken = false) {
    appendUserMessage(text);
    conversationHistory.push({ role: 'user', content: text });

    // Show typing
    if (typingIndicator) typingIndicator.classList.add('show');
    scrollToBottom();

    try {
      const res = await fetch('/api/chatbot', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          message: text,
          history: conversationHistory.slice(-6),
        }),
      });

      const data = await res.json();
      if (typingIndicator) typingIndicator.classList.remove('show');

      if (res.ok) {
        appendBotMessage(data.reply, data.voice_text, data.quick_actions, data.policy_data);
        conversationHistory.push({ role: 'assistant', content: data.reply });

        // Speak reply if voice enabled or user spoke
        if (isVoiceEnabled) {
          speakText(data.voice_text || data.reply);
        }
      } else {
        appendBotMessage("I apologize, but I encountered a temporary issue. Please ask again.");
      }
    } catch (err) {
      console.error('Chatbot error:', err);
      if (typingIndicator) typingIndicator.classList.remove('show');
      appendBotMessage("It looks like there's a connection issue. Please check your network and try again.");
    }
  }

  // UI Helpers
  function appendUserMessage(text) {
    const bubble = document.createElement('div');
    bubble.className = 'chat-bubble user';
    bubble.textContent = text;
    chatMessages.insertBefore(bubble, typingIndicator);
    scrollToBottom();
  }

  function appendBotMessage(markdownText, voiceText = null, quickActions = [], policyData = null) {
    const bubble = document.createElement('div');
    bubble.className = 'chat-bubble bot';

    const formattedHtml = formatMarkdown(markdownText);
    bubble.innerHTML = formattedHtml;

    // Speaker replay button
    const speechButton = document.createElement('button');
    speechButton.type = 'button';
    speechButton.className = 'bot-speaker-icon';
    speechButton.innerHTML = `
      <svg style="width:12px; height:12px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
        <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
      </svg>
      Listen
    `;
    speechButton.addEventListener('click', () => {
      speakText(voiceText || markdownText);
    });
    bubble.appendChild(speechButton);

    // If quick actions exist, append them
    if (quickActions && quickActions.length > 0) {
      const actionsWrap = document.createElement('div');
      actionsWrap.className = 'chat-quick-actions';
      actionsWrap.style.marginTop = '10px';

      quickActions.forEach(actionText => {
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'chip-btn';
        chip.textContent = actionText;
        chip.addEventListener('click', () => {
          handleQuickAction(actionText);
        });
        actionsWrap.appendChild(chip);
      });

      bubble.appendChild(actionsWrap);
    }

    chatMessages.insertBefore(bubble, typingIndicator);
    scrollToBottom();
  }

  function handleQuickAction(actionText) {
    if (actionText === 'Fill in Calculator' || actionText === 'Scroll to Calculator') {
      document.getElementById('calculatorSection')?.scrollIntoView({ behavior: 'smooth' });
      sendMessage(actionText);
    } else if (actionText.startsWith('Select ') && actionText.includes('Plan')) {
      const plan = actionText.includes('Plus') ? 'PLUS' : (actionText.includes('Premium') ? 'PREMIUM' : 'BASIC');
      const planCodeSelect = document.getElementById('plan_code');
      if (planCodeSelect) {
        planCodeSelect.value = plan;
        planCodeSelect.dispatchEvent(new Event('change'));
      }
      document.getElementById('calculatorSection')?.scrollIntoView({ behavior: 'smooth' });
      sendMessage(actionText);
    } else {
      sendMessage(actionText);
    }
  }

  function formatMarkdown(text) {
    if (!text) return '';
    let html = escapeHtml(text);
    // Bold: **text**
    html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    // Code / pills: `text`
    html = html.replace(/`(.*?)`/g, '<code style="background:#E2E8F0; padding:2px 5px; border-radius:4px; font-size:0.85em;">$1</code>');
    // Bullet points: • item
    html = html.replace(/• (.*?)(?=(• |$|<br>|\n))/g, '<div style="margin-left: 12px; margin-bottom: 3px;">• $1</div>');
    // Line breaks
    html = html.replace(/\n\n/g, '<div style="height: 8px;"></div>');
    html = html.replace(/\n/g, '<br>');
    return html;
  }

  function escapeHtml(str) {
    return str
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;");
  }

  function scrollToBottom() {
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  // Handle global quick triggers
  window.toggleChat = toggleChat;
  window.askMaya = function(query) {
    if (!isChatOpen) toggleChat();
    sendMessage(query);
  };
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initMayaChat);
} else {
  initMayaChat();
}
