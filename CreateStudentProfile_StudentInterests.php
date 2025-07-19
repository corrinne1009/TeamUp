<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Team Up</title>
  <link rel="stylesheet" href="TeamUp.css" />
</head>
<?php
session_start();
// fetch previously selected interests if needed...
?>
<body>
  <header>
    <?php include 'Navbar.php'; ?>
  </header>

  <div class="card-container">
    <div class="card layout">
      <nav class="sidebar">
        <ul>
          <li><a href="#">Bio</a></li>
          <li><a href="#">Interests</a></li>
          <li><a href="#">Skills</a></li>
          <li><a href="#">Availability</a></li>
        </ul>
      </nav>

      <main class="content">
        <form action="submitStudentProfileInterests.php" method="post">
          <div class="icon-grid">
            <button type="button" class="icon-button">
              <img src="TeamUp_icons/interests_art.png" alt="Art" />
              <span class="icon-label">Art</span>
            </button>

            <button type="button" class="icon-button">
              <img src="TeamUp_icons/interests_cooking.png" alt="Cooking" />
              <span class="icon-label">Cooking</span>
            </button>

            <button type="button" class="icon-button">
              <img src="TeamUp_icons/interests_family.png" alt="Family" />
              <span class="icon-label">Family</span>
            </button>

            <button type="button" class="icon-button">
              <img src="TeamUp_icons/interests_fitness.png" alt="Fitness" />
              <span class="icon-label">Fitness</span>
            </button>

            <button type="button" class="icon-button">
              <img src="TeamUp_icons/interests_gaming.png" alt="Gaming" />
              <span class="icon-label">Gaming</span>
            </button>

            <button type="button" class="icon-button">
              <img src="TeamUp_icons/interests_gardening.png" alt="Gardening" />
              <span class="icon-label">Gardening</span>
            </button>

            <button type="button" class="icon-button">
              <img src="TeamUp_icons/interests_movies.png" alt="Movies" />
              <span class="icon-label">Movies</span>
            </button>

            <button type="button" class="icon-button">
              <img src="TeamUp_icons/interests_music.png" alt="Music" />
              <span class="icon-label">Music</span>
            </button>

            <button type="button" class="icon-button">
              <img src="TeamUp_icons/interests_pets.png" alt="Pets" />
              <span class="icon-label">Pets</span>
            </button>

            <button type="button" class="icon-button">
              <img src="TeamUp_icons/interests_reading.png" alt="Reading" />
              <span class="icon-label">Reading</span>
            </button>

            <button type="button" class="icon-button">
              <img src="TeamUp_icons/interests_sports.png" alt="Sports" />
              <span class="icon-label">Sports</span>
            </button>

            <button type="button" class="icon-button">
              <img src="TeamUp_icons/interests_traveling.png" alt="Travel" />
              <span class="icon-label">Travel</span>
            </button>
          </div>

          <div class="navigation-buttons">
            <button type="submit" formaction="CreateStudentProfile_StudentBio.php" class="NavButton">Back</button>
            <button type="submit" class="NavButton">Next</button>
          </div>
          <div id="selectedInterests"></div>
        </form>
      </main>
    </div>
  </div>
<script>
  const iconButtons = document.querySelectorAll('.icon-button');
  const selectedInterestsContainer = document.getElementById('selectedInterests');

  iconButtons.forEach(button => {
    button.addEventListener('click', () => {
      const selected = document.querySelectorAll('.icon-button.selected');

      if (button.classList.contains('selected')) {
        button.classList.remove('selected');
      } else if (selected.length < 3) {
        button.classList.add('selected');
      } else {
        alert('You can only select up to 3 interests.');
      }
    });
  });

  document.querySelector('form').addEventListener('submit', function () {
    selectedInterestsContainer.innerHTML = ''; // clear previous

    const selected = document.querySelectorAll('.icon-button.selected');
    selected.forEach((btn, index) => {
      const label = btn.querySelector('.icon-label').innerText;
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = `interest${index + 1}`;
      input.value = label;
      selectedInterestsContainer.appendChild(input);
    });
  });
</script>
  <footer>
    <!-- Optional footer content -->
  </footer>
</body>
</html>