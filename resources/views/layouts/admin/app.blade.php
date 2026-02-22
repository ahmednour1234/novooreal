<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
        @php $fingerprint = env('SYSTEM_FINGERPRINT', 'kyuretwertyeuoqplsdamajeurhewqurqbqwjebuwqheuwqh256449797991164948899765165165897978798789'); @endphp
    <meta name="system-fingerprint" content="{{ $fingerprint }}">
    <meta name="viewport" content="width=device-width">
    <!-- Title -->
    <title>@yield('title')</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="">
    <!-- Font -->
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/google-fonts.css">
        <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/fonts/Bahij_TheSansArabic-Plain.ttf">

    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/vendor.min.css">
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/vendor/icon-set/style.css">
    <!-- CSS Front Template -->
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/theme.minc619.css?v=1.0">
    <!-- select picker -->
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/bootstrap-select.min.css"/>
    @stack('css_or_js')

    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/custom.css"/>

    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/toastr.css">
</head>
<style>
    #sidebarMain{
            background-color:red;

  }
  .table {
    width: 100%;
    margin-bottom: 1rem;
    color: black;
        background-color: #fff;

}
thead {
    display: table-header-group;
    vertical-align: middle;
    unicode-bidi: isolate;
    border-color: #fff;
    color: black;
}
.table .thead-light th {
    color: #8B909A;
    background-color: white;
    border-color: rgba(231, 234, 243, .7);
}
.text-primary {
    color:#3c4b96 !important;
}#chatbot-window {
    position: fixed;
    bottom: 20px;
    left: 20px;
    width: 350px;
    height: 500px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    display: none;
    flex-direction: column;
    overflow: hidden;
}

.chat-header {
    background: linear-gradient(90deg, #003f88, #003f88);
    color: white;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: bold;
    border-radius: 15px 15px 0 0;
}

.chat-messages {
    flex: 1;
    padding: 10px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 8px;
    background: #f9f9f9;
}

.message {
    padding: 10px 15px;
    margin: 5px;
    border-radius: 20px;
    max-width: 80%;
    font-size: 14px;
    display: inline-block;
}

.user-message {
    background: #001B63;
    color: white;
    align-self: flex-start;
    border-radius: 20px 20px 20px 5px;
}

.bot-message {
    background: #ffffff;
    color: black;
    align-self: flex-end;
    border-radius: 20px 20px 5px 20px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
}

.chat-footer {
    padding: 10px;
    display: flex;
    align-items: center;
    background: white;
    border-top: 1px solid #ddd;
}

.chat-footer input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 20px;
    font-size: 14px;
}

.chat-footer button {
    background: #003f88;
    color: white;
    border: none;
    padding: 8px 15px;
    margin-left: 8px;
    cursor: pointer;
    border-radius: 20px;
    font-size: 14px;
}

.chat-footer button:hover {
    background: #3c4b96;
}

.faq-buttons {
    padding: 10px;
    border-top: 1px solid #ddd;
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    background: #fff;
}

.faq-buttons button {
    background: #f1f1f1;
    border: none;
    padding: 8px 12px;
    font-size: 14px;
    cursor: pointer;
    border-radius: 15px;
}

.faq-buttons button:hover {
    background: #ddd;
}

#chatbot-btn {
    position: fixed;
    bottom: 20px;
    left: 20px;
    background: #003f88;
    color: white;
    border: none;
    padding: 12px 16px;
    font-size: 18px;
    border-radius: 50px;
    cursor: pointer;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

#chatbot-btn:hover {
    background: #001B63;
}
#close-chatbot {
    background: transparent;
    border: none;
    color: white;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.3s ease;
}

#close-chatbot:hover {
    color: #ff4d4d; /* Light red on hover */
}
  .suggestions-container {
    position: absolute;
    background: white;
    border: 1px solid #ccc;
    width: calc(100% - 20px);
    max-height: 150px;
    overflow-y: auto;
    display: none; /* إخفاء افتراضي حتى تظهر الاقتراحات */
    z-index: 1000;
}

.suggestion-item {
    padding: 8px;
    cursor: pointer;
}

.suggestion-item:hover {
    background: #f0f0f0;
}
.btn-primary {
    color: #fff;
    background-color: #003f88;
    border-color: #003f88;
}
.bg-danger {
    background-color: #EE6055 !important;
}
.bg-success {
    background-color: #60D394 !important;
}
.toggle-switch-input:checked + .toggle-switch-label {
    background-color: #60D394;
}
.h1, .h2, .h3, .h4, .h5, .h6, h1, h2, h3, h4, h5, h6 {
    color: #1e2022;
    font-size:18px;
}
h1{
    color: #1e2022;
    font-size:18px;
}
.btn-outline-info {
    color: #677788;
    border-color: #677788;
}
.btn-outline-info:hover {
    color: #fff;
    background-color: #003f88;
    border-color: #003f88;
}
.btn-outline-success {
    color: #677788;
    border-color: #677788;
}
.btn-outline-success:hover {
    color: #fff;
    background-color: #003f88;
    border-color: #003f88;
}
.btn-success {
    color: #fff;
    background-color: #708D81;
    border-color: #708D81;
}
.btn-danger {
    color: #fff;
    background-color: #BF0603;
    border-color: #BF0603;
}
.btn-info {
    color: #fff;
    background-color: #F4D58D;
    border-color: #F4D58D;
}
.btn-info:hover {
    color: #fff;
    background-color: #F4D58D;
    border-color: #F4D58D;
}
.main .content {
    padding-top: 0.3rem;
    padding-bottom: 1.75rem;
}
.badge {
    background-color: #708D81;
    color: white;
}
.text-danger {
    color: #BF0603 !important;
}
.text-success {
    color: #003f88 !important;
}
.btn-outline-primary:not(:disabled):not(.disabled).active, .btn-outline-primary:not(:disabled):not(.disabled):active, .show > .btn-outline-primary.dropdown-toggle {
    color: #fff;
    background-color: #003f88;
    border-color: #003f88;
}
.btn-primary:not(:disabled):not(.disabled).active, .btn-primary:not(:disabled):not(.disabled):active, .show > .btn-primary.dropdown-toggle {
    color: #fff;
    background-color: #F4D58D;
    border-color: #F4D58D;
}
.btn-info:not(:disabled):not(.disabled).active, .btn-info:not(:disabled):not(.disabled):active, .show > .btn-info.dropdown-toggle {
    color: #fff;
    background-color: #F4D58D;
    border-color: #F4D58D
}

.btn-info:not(:disabled):not(.disabled).active:focus, .btn-info:not(:disabled):not(.disabled):active:focus, .show > .btn-info.dropdown-toggle:focus {
    box-shadow: #F4D58D;
}


.btn-info.disabled, .btn-info:disabled {
    color: #fff;
    background-color: #F4D58D;
    border-color: #F4D58D;
}
.btn-primary:not(:disabled):not(.disabled).active, .btn-primary:not(:disabled):not(.disabled):active, .show > .btn-primary.dropdown-toggle {
    color: #fff;
    background-color: #003f88;
    border-color: #003f88;
}
.btn-danger.disabled, .btn-danger:disabled {
    color: #fff;
    background-color: #BF0603;
    border-color: #BF0603;
}
.btn-danger:hover {
    color: #fff;
    background-color: #BF0603;
    border-color: #BF0603;
}
.btn.disabled, .btn:disabled {
    opacity: .4;
}
.table td, .table th {
    vertical-align: center;
    font-size:0.8rem ;
}
 .table th {
    vertical-align: center;
    font-size:0.8rem ;
    background-color: #EDF2F4;
    color: black;
}
.bg-secondary {
    background-color:#71869d !important;
}
</style>
<body class="footer-offset">

    <!-- Toggler -->
    <!--<div class="direction-toggle">-->
    <!--    <i class="tio-settings"></i>-->
    <!--    <span></span>-->
    <!--</div>-->
    <!-- Toggler -->

<!--{{--loader--}}-->
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div id="loading" class="d-none">
                <div class="loader-img">
                    <img width="200" src="{{asset('public/assets/admin/img/loader.gif')}}">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS Preview mode only -->
@include('layouts.admin.partials._header')
@include('layouts.admin.partials._sidebar')
<!-- END ONLY DEV -->

<main id="content" role="main" class="main pointer-event">
    <!-- Content -->
@yield('content')
<!-- End Content -->

    <!-- Footer -->
@include('layouts.admin.partials._footer')
<!-- End Footer -->

    <div class="modal fade" id="popup-modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <center>
                                <h2 class="title-new-order">
                                    <i class="tio-shopping-cart-outlined"></i> {{\App\CPU\translate('You_have_new_order,_Check_Please')}}.
                                </h2>
                                <hr>
                                <button onclick="check_order()" class="btn btn-primary">{{\App\CPU\translate('Ok,_let_me_check')}}</button>
                            </center>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<button id="chatbot-btn">💬</button>

<div id="chatbot-window">
    <div class="chat-header">
        <span>مساعد الذكاء الاصطناعي</span>
        <button id="close-chatbot">✖</button>
    </div>
    <div class="chat-messages" id="chat-messages"></div>
    <div class="faq-buttons">
        <button class="faq-btn">ما هي خدماتكم؟</button>
        <button class="faq-btn">كيف يمكنني التواصل؟</button>
        <button class="faq-btn">هل لديكم تطبيق؟</button>
        <button class="faq-btn">ما هي ساعات العمل؟</button>
        <button class="faq-btn">كيف يمكنني تقديم شكوى؟</button>
    </div>
    <div class="chat-footer">
        <input type="text" id="chat-input-field" placeholder="اكتب رسالتك...">
        <button id="send-message">إرسال</button>
    </div>
    <div id="suggestions" class="suggestions-container"></div>

</div>


</main>

<!-- حاوية عرض الاقتراحات -->


<!-- ========== END MAIN CONTENT ========== -->

<!-- ========== END SECONDARY CONTENTS ========== -->
<script src="{{asset('public/assets/admin')}}/js/custom.js"></script>
<!-- JS Implementing Plugins -->
<script>
    document.getElementById("chatbot-btn").addEventListener("click", function () {
        document.getElementById("chatbot-window").style.display = "flex";
        setTimeout(() => {
            showBotMessage("💡 نظام Novoo  يرحب بكم! نحن في خدمتكم 24 ساعة.");
        }, 500);
    });

    document.getElementById("close-chatbot").addEventListener("click", function () {
        document.getElementById("chatbot-window").style.display = "none";
    });

    document.getElementById("send-message").addEventListener("click", function () {
        sendMessage();
    });

    document.querySelectorAll(".faq-btn").forEach(button => {
        button.addEventListener("click", function () {
            document.getElementById("chat-input-field").value = this.textContent;
            sendMessage();
        });
    });

    document.getElementById("chat-input-field").addEventListener("input", function () {
        let query = this.value.trim();
        if (query.length > 1) {
            fetchSuggestions(query);
        } else {
            document.getElementById("suggestions").style.display = "none";
        }
    });

    function fetchSuggestions(query) {
        fetch("/chatbot/suggestions", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
            body: JSON.stringify({ query: query })
        })
        .then(response => response.json())
        .then(data => {
            displaySuggestions(data.suggestions);
        })
        .catch(error => console.error("Error fetching suggestions:", error));
    }

    function displaySuggestions(suggestions) {
        let suggestionsContainer = document.getElementById("suggestions");
        suggestionsContainer.innerHTML = "";
        if (suggestions.length === 0) {
            suggestionsContainer.style.display = "none";
            return;
        }

        suggestions.forEach(suggestion => {
            let suggestionItem = document.createElement("div");
            suggestionItem.classList.add("suggestion-item");
            suggestionItem.textContent = suggestion;
            suggestionItem.addEventListener("click", function () {
                document.getElementById("chat-input-field").value = suggestion;
                sendMessage();
                suggestionsContainer.style.display = "none";
            });
            suggestionsContainer.appendChild(suggestionItem);
        });

        suggestionsContainer.style.display = "block";
    }

    function sendMessage() {
        let message = document.getElementById("chat-input-field").value.trim();
        if (message === "") return;

        showUserMessage(message);
        document.getElementById("chat-input-field").value = "";
        document.getElementById("suggestions").style.display = "none"; // إخفاء الاقتراحات عند الإرسال

        let typingIndicator = showBotMessage("✍️ البوت يكتب...");

        fetch("/chatbot", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            typingIndicator.remove();
            showBotMessage(data.reply);
        })
        .catch(error => {
            typingIndicator.remove();
            showBotMessage("❌ حدث خطأ، حاول مرة أخرى.");
            console.error("Error:", error);
        });
    }

    function showUserMessage(message) {
        let userMessage = document.createElement("div");
        userMessage.classList.add("message", "user-message");
        userMessage.textContent = message;
        document.getElementById("chat-messages").appendChild(userMessage);
        scrollToBottom();
    }

    function showBotMessage(message) {
        let botMessage = document.createElement("div");
        botMessage.classList.add("message", "bot-message");
        botMessage.textContent = message;
        document.getElementById("chat-messages").appendChild(botMessage);
        scrollToBottom();
        return botMessage;
    }

    function scrollToBottom() {
        let chatMessages = document.getElementById("chat-messages");
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
</script>
@stack('script')

<!-- JS Front -->
<script src="{{asset('public/assets/admin')}}/js/vendor.min.js"></script>
<script src="{{asset('public/assets/admin')}}/js/theme.min.js"></script>
<script src="{{asset('public/assets/admin')}}/js/sweet_alert.js"></script>
<script src="{{asset('public/assets/admin')}}/js/toastr.js"></script>
<!-- select picker -->
<script src="{{asset('public/assets/admin')}}/js/bootstrap-select.min.js"></script>
<!-- ck editor -->
<script src="{{asset('public/assets/admin')}}/js/ck-editor.js"></script>
{!! Toastr::message() !!}

@if ($errors->any())
    <script>
        @foreach($errors->all() as $error)
        toastr.error('{{$error}}', Error, {
            CloseButton: true,
            ProgressBar: true
        });
        @endforeach
    </script>
@endif
<!-- Toggle Direction Init -->
<script>
    $(document).ready(function () {
        // التحقق من الاتجاه المخزن في LocalStorage
        let direction = localStorage.getItem("direction") || "rtl"; // القيمة الافتراضية RTL

        // تطبيق الاتجاه عند تحميل الصفحة
        setDirection(direction);

        $(".direction-toggle").on("click", function () {
            // تغيير الاتجاه عند النقر
            let newDirection = (localStorage.getItem("direction") === "rtl") ? "ltr" : "rtl";
            setDirection(newDirection);
        });

        function setDirection(direction) {
            localStorage.setItem("direction", direction);
            $("html").attr("dir", direction);
            $(".direction-toggle").find("span").text(direction === "rtl" ? "Switch to LTR" : "Switch to RTL");
        }

        // تمرير القائمة الجانبية إلى العنصر النشط
        if ($(".navbar-vertical-content li.active").length) {
            $(".navbar-vertical-content").animate({
                scrollTop: $(".navbar-vertical-content li.active").offset().top - 150
            }, 10);
        }
    });
</script>


<!-- JS Plugins Init. -->
<script src="{{asset('public/assets/admin')}}/js/app-page.js"></script>

<audio id="myAudio">
    <source src="{{asset('public/assets/admin/sound/notification.mp3')}}" type="audio/mpeg">
</audio>

</body>
</html>
