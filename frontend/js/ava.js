document.getElementById('uploadButton').addEventListener('click', function() {
    document.getElementById('avatarInput').click(); // Симулируем клик по скрытому input
});

document.getElementById('avatarInput').addEventListener('change', function() {
    // Отправляем форму автоматически после выбора файла
    document.getElementById('avatarUploadForm').submit();
});