<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PrimeHealth Clinic Header</title>
<link rel="stylesheet" href="../assets/header.css">
</head>
<body>

<header>
    <div class="title">PrimeHealth Clinic</div>
    <div class="datetime" id="datetime"></div>
</header>

<script>
    function updateDateTime() {
        const now = new Date();

        const options = { 
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
            hour: '2-digit', minute: '2-digit', second: '2-digit'
        };

        document.getElementById('datetime').textContent = now.toLocaleString('en-US', options);
    }

    // Update immediately
    updateDateTime();
    // Update every second
    setInterval(updateDateTime, 1000);
</script>

</body>
</html>