<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Team Up</title>
  <link rel="stylesheet" href="TeamUp.css" />
</head>
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
          <li><a href="#" class="active">Skills</a></li>
          <li><a href="#">Availability</a></li>
        </ul>
      </nav>

      <main class="content">
        <form action="submitStudentProfileTeamwork.php" method="post">
          <fieldset>
            <legend>1. Which best describes your preferred working style in a group setting?</legend>
            <label><input type="radio" name="q1" value="I like to take the lead and organize tasks"> I like to take the lead and organize tasks</label><br>
            <label><input type="radio" name="q1" value="I prefer to collaborate and discuss ideas as a group"> I prefer to collaborate and discuss ideas as a group</label><br>
            <label><input type="radio" name="q1" value="I work best independently and contribute my part"> I work best independently and contribute my part</label><br>
            <label><input type="radio" name="q1" value="I’m adaptable and fill in where needed"> I’m adaptable and fill in where needed</label>
          </fieldset>

          <fieldset>
            <legend>2. How do you prefer to communicate with your teammates outside of class?</legend>
            <label><input type="radio" name="q2" value="Group chats (e.g., GroupMe, Discord, Slack)"> Group chats (e.g., GroupMe, Discord, Slack)</label><br>
            <label><input type="radio" name="q2" value="Video or voice calls"> Video or voice calls</label><br>
            <label><input type="radio" name="q2" value="Email or school platform messages"> Email or school platform messages</label><br>
            <label><input type="radio" name="q2" value="In-person meetings"> In-person meetings</label>
          </fieldset>

          <fieldset>
            <legend>3. How would you rate your experience with front-end development?</legend>
            <label><input type="radio" name="q3" value="I’m new to front-end development"> I’m new to front-end development</label><br>
            <label><input type="radio" name="q3" value="I’ve explored front-end basics through tutorials or coursework"> I’ve explored front-end basics through tutorials or coursework</label><br>
            <label><input type="radio" name="q3" value="I’ve built a functioning front-end for a project"> I’ve built a functioning front-end for a project</label><br>
            <label><input type="radio" name="q3" value="I’m confident using frameworks and can troubleshoot front-end issues"> I’m confident using frameworks and can troubleshoot front-end issues</label><br>
            <label><input type="radio" name="q3" value="I’ve led front-end development for a project or team"> I’ve led front-end development for a project or team</label>
          </fieldset>

          <fieldset>
            <legend>4. How would you rate your experience with back-end development?</legend>
            <label><input type="radio" name="q4" value="I’m new to back-end development"> I’m new to back-end development</label><br>
            <label><input type="radio" name="q4" value="I’ve done some coursework or followed tutorials"> I’ve done some coursework or followed tutorials</label><br>
            <label><input type="radio" name="q4" value="I’ve built server-side functionality or integrated a database"> I’ve built server-side functionality or integrated a database</label><br>
            <label><input type="radio" name="q4" value="I’m comfortable with deploying and maintaining back-end systems"> I’m comfortable with deploying and maintaining back-end systems</label><br>
            <label><input type="radio" name="q4" value="I’ve led or contributed significantly to back-end systems for a project or class"> I’ve led or contributed significantly to back-end systems for a project or class</label>
          </fieldset>

          <fieldset>
            <legend>5. What strengths or resources do you bring to a group project?</legend>
            <label><input type="radio" name="q5" value="Strong writing or documentation skills"> Strong writing or documentation skills</label><br>
            <label><input type="radio" name="q5" value="Technical or coding abilities"> Technical or coding abilities</label><br>
            <label><input type="radio" name="q5" value="Creative problem-solving"> Creative problem-solving</label><br>
            <label><input type="radio" name="q5" value="Project management or leadership"> Project management or leadership</label><br>
            <label><input type="radio" name="q5" value="Research and information gathering"> Research and information gathering</label>
          </fieldset>

          <div class="navigation-buttons">
            <button class="btn-login" type="submit" formaction="CreateStudentProfile_StudentInterests.php">Back</button>
            <button class="btn-login" type="submit">Next</button>
          </div>
        </form>
      </main>
    </div>
  </div>

  <footer>
    <!-- Optional footer content -->
  </footer>
</body>
</html>