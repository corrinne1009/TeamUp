<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8">
  <title>About TeamUp</title>
  <link rel="stylesheet" href="TeamUp.css"> <!-- Optional: reuse existing styles -->
</head>
<body>

<header>
  
  <?php include 'Navbar.php'; ?>
</header>

<main class="about-content">
  <div class="text-column">
  <h1 class="page-title">About TeamUp</h1>
  <section class="mission">
    <h2>Our Mission</h2>
    <p>
      TeamUp was built to help students thrive through collaboration. Our goal is to create better-balanced student teams
      by combining availability, skill diversity, and shared interests—because great learning happens when everyone brings
      something unique to the table.
    </p>
    <p>
      Whether you're developing apps, solving problems, or creating research projects, TeamUp uses smart algorithms and instructor guidance
      to form groups that are both cohesive and complementary.
    </p>
  </section>
  
  <section class="how-it-works">
    <h2>How It Works</h2>
    <ul>
      <li>Matches students with overlapping schedules to increase availability</li>
      <li>Balances skills across teams to encourage peer learning</li>
      <li>Connects people with shared interests to foster team chemistry</li>
    </ul>
    <p>
      Instructors can guide the process by choosing how teams are formed—setting priorities, reviewing drafts, and supporting students every step of the way.
    </p>
  </section>

  <section class="impact">
    <h2>Why It Matters</h2>
    <p>
      Students who feel supported and aligned in their groups are more likely to stay engaged, contribute meaningfully,
      and grow from diverse perspectives. TeamUp turns group work from guesswork into growth.
    </p>
  </section>
  </div>
  <div class="image-column">
      <img src="teamup1.jpg" alt="Students collaborating">
      <img src="teamup2.jpg" alt="Algorithm dashboard">
  </div>
</main>

<footer>
  <p>&copy; <?= date("Y") ?> TeamUp | Created to elevate learning, together.</p>
</footer>

</body>
</html>