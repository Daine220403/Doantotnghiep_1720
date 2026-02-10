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

  // minimal bot reply (demo)
  const botReply = (text) => {
    const t = text.toLowerCase();
    let reply =
      "Anh cho em biết điểm đến + ngày dự kiến đi + số người để em gợi ý tour phù hợp nhé 🙂";

    if (t.includes("trong nước") || t.includes("trong nuoc")) {
      reply = "Dạ tour trong nước ok anh. Anh thích biển (Phú Quốc/Đà Nẵng) hay núi (Đà Lạt/Sapa) ạ?";
    } else if (t.includes("quốc tế") || t.includes("quoc te")) {
      reply = "Dạ tour quốc tế ok anh. Anh thích Thái Lan, Singapore hay Hàn Quốc ạ?";
    } else if (t.includes("giá") || t.includes("gia")) {
      reply = "Anh dự kiến ngân sách khoảng bao nhiêu và đi mấy ngày để em báo giá tour sát nhất nha.";
    } else if (t.includes("liên hệ") || t.includes("lien he")) {
      reply = "Anh có thể gọi 1900 1234 hoặc để lại SĐT, bên em sẽ gọi tư vấn ngay ạ.";
    }

    // simulate typing delay
    setTimeout(() => addBubble(reply, "bot"), 400);
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
