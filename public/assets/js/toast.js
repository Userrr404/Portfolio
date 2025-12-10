// Toast function (global)
function showToast(type, message) {
    const box = document.getElementById("toastBox");
    const toast = document.createElement("div");

    toast.className = "toast " + type;
    toast.innerText = message;

    box.appendChild(toast);

    setTimeout(() => toast.classList.add("show"), 20);
    setTimeout(() => {
        toast.classList.remove("show");
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

// Global AJAX file download utility
async function ajaxDownload(url, filename = "downloaded_file") {
    try {
        const response = await fetch(url);
        const type = response.headers.get("Content-Type");

        // If server sent JSON → it's an error
        if (type && type.includes("application/json")) {
            const data = await response.json();
            showToast("error", data.message || "Something went wrong");
            return;
        }

        // SUCCESS
        const blob = await response.blob();
        const link = document.createElement("a");
        const tempUrl = URL.createObjectURL(blob);

        link.href = tempUrl;
        link.download = filename;
        link.click();

        URL.revokeObjectURL(tempUrl);

        showToast("success", "Download started...");
    } 
    catch (err) {
        showToast("error", "Failed to download.");
    }
}
