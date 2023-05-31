<!DOCTYPE html>
<html>

<head>
    <title>Yandex Music Parser</title>
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #91a4b1;
        }
    </style>
</head>

<body>
    <div class="flex justify-center items-center h-screen">
        <div class="bg-white rounded-lg p-8 max-w-sm">
            <h1 class="text-2xl mb-4">Yandex Music Parser</h1>

            <form method="POST" action='/' class="flex flex-col items-center">
                @csrf
                <div class="mb-4">
                    <label for="url" class="block text-center">Yandex Music URL:</label>
                    <input type="text" id="url" name="url" required
                        class="border-gray-300 border rounded-md px-4 py-2 w-full">
                </div>

                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md">Parse</button>
            </form>
        </div>
    </div>
</body>

</html>