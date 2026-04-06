// Chatbot toggle + simple UI messages
document.addEventListener("DOMContentLoaded", () => {
  const toggleBtn = document.getElementById("chatbot-toggle");
  const closeBtn = document.getElementById("chatbot-close");
  const panel = document.getElementById("chatbot-panel");

  const form = document.getElementById("chatbot-form");
  const input = document.getElementById("chatbot-input");
  const messages = document.getElementById("chatbot-messages");
  const quickBtns = document.querySelectorAll(".chatbot-quick");

  if (!toggleBtn || !panel) return;

  const open = () => {
    panel.classList.remove("hidden");
    // focus input a bit later so panel is visible
    setTimeout(() => input?.focus(), 50);
  };

  const close = () => {
    panel.classList.add("hidden");
  };

  toggleBtn.addEventListener("click", () => {
    panel.classList.contains("hidden") ? open() : close();
  });

  closeBtn?.addEventListener("click", close);

  // helper: add message bubble
  const addBubble = (text, from = "user") => {
    const wrap = document.createElement("div");
    wrap.className = from === "user" ? "flex justify-end" : "flex items-start gap-2";

    if (from === "user") {
      wrap.innerHTML = `
        <div class="max-w-[80%] rounded-2xl rounded-tr-md bg-blue-600 px-3 py-2 text-sm text-white">
          ${escapeHtml(text)}
        </div>
      `;
    } else {
      wrap.innerHTML = `
        <div class="w-8 h-8 rounded-full bg-sky-600 text-white flex items-center justify-center text-xs font-bold">VT</div>
        <div class="max-w-[80%] rounded-2xl rounded-tl-md bg-white border border-gray-200 px-3 py-2 text-sm text-gray-800">
          ${text}
        </div>
      `;
    }

    messages.appendChild(wrap);
    messages.scrollTop = messages.scrollHeight;
  };

  // call backend chatbot (Gemini)
  const botReply = async (text) => {
    // show temporary "đang soạn" bubble
    const loadingId = `loading-${Date.now()}`;
    const loadingWrap = document.createElement("div");
    loadingWrap.id = loadingId;
    loadingWrap.className = "flex items-start gap-2";
    loadingWrap.innerHTML = `
      <div class="w-8 h-8 rounded-full bg-sky-600 text-white flex items-center justify-center text-xs font-bold">VT</div>
      <div class="max-w-[80%] rounded-2xl rounded-tl-md bg-white border border-gray-200 px-3 py-2 text-sm text-gray-500 italic">
        Đang soạn câu trả lời...
      </div>
    `;
    messages.appendChild(loadingWrap);
    messages.scrollTop = messages.scrollHeight;

    try {
      const tokenMeta = document.querySelector('meta[name="csrf-token"]');
      const csrfToken = tokenMeta ? tokenMeta.getAttribute("content") : "";

      const res = await fetch("/chatbot/message", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrfToken,
          Accept: "application/json",
        },
        body: JSON.stringify({ message: text }),
      });

      // remove loading bubble
      loadingWrap.remove();

      if (!res.ok) {
        addBubble(
          "Xin lỗi, hệ thống đang bận. Anh/chị vui lòng thử lại sau ít phút.",
          "bot"
        );
        return;
      }

      const data = await res.json();
      const reply = data.reply ||
        "Xin lỗi, em chưa hiểu rõ câu hỏi. Anh/chị có thể diễn đạt lại giúp em với ạ?";

      addBubble(reply, "bot");
    } catch (e) {
      loadingWrap.remove();
      addBubble(
        "Xin lỗi, hệ thống đang gặp lỗi. Anh/chị vui lòng thử lại sau ít phút.",
        "bot"
      );
    }
  };

  // submit handler
  form?.addEventListener("submit", (e) => {
    e.preventDefault();
    const text = input.value.trim();
    if (!text) return;
    addBubble(text, "user");
    input.value = "";
    botReply(text);
  });

  // quick reply buttons
  quickBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      const text = btn.textContent?.trim() || "";
      if (!text) return;
      addBubble(text, "user");
      botReply(text);
    });
  });

  // basic HTML escape for user messages
  function escapeHtml(str) {
    return str
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }
});
