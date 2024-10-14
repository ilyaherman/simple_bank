<form id="transferForm">
    <input type="number" name="to_user_id" placeholder="Recipient User ID" required>
    <input type="number" step="0.01" name="amount" placeholder="Amount" required>
    <button type="submit">Transfer</button>
</form>

<script>
document.getElementById('transferForm').onsubmit = async function (e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const response = await fetch('/backend/controllers/TransactionController.php', {
        method: 'POST',
        body: new URLSearchParams([...formData])
    });
    const result = await response.json();
    alert(result.message);
    
    if (result.status === 'success') {
        window.location.reload(); // Перезагрузим страницу для обновления баланса и транзакций
    }
};
</script>
