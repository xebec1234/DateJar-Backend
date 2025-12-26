<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DateJar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen flex items-center justify-center
    bg-linear-to-b from-white via-white to-[#E0EFF4]">

    <div
        class="
        flex flex-col text-center
        max-w-lg w-full
        rounded-4xl

        bg-white/80 backdrop-blur-2xl
        border border-white/60

        shadow-[20px_20px_50px_rgba(0,0,0,0.12),
                -20px_-20px_50px_rgba(255,255,255,0.95)]
        overflow-hidden
        "
        style="animation: float 4s ease-in-out infinite;"
    >
        <!-- Image fills the card width -->
        <img
            src="https://c.tenor.com/72341xps0RoAAAAC/tenor.gif"
            alt="Luffy Smile"
            class="w-full h-auto object-cover"
        >

        <!-- Text section keeps padding -->
        <div class="p-10">
            <p class="text-slate-700 text-lg tracking-wide opacity-90">
                There is nothing to see here 👀
            </p>
        </div>
    </div>


    <style>
        @keyframes float {
            0% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0); }
        }
    </style>
</body>
</html>
