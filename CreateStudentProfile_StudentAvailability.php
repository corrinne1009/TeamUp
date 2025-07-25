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
          <li><a href="#">Skills</a></li>
          <li><a href="#" class="active">Availability</a></li>
        </ul>
      </nav>

      <main class="content">
        <form action="submitStudentProfileAvailability.php" method="post">
          <h2>Weekly Availability</h2>
          <p>Select all time slots you're available for each day:</p>

          <table border="1" cellpadding="8">
            <thead>
              <tr>
                <th></th>
                <th>Morning<br><small>(6:00 AM – 12:00 PM)</small></th>
                <th>Afternoon<br><small>(12:00 PM – 5:00 PM)</small></th>
                <th>Evening<br><small>(5:00 PM – 10:00 PM)</small></th>
                <th>Night<br><small>(10:00 PM – 6:00 AM)</small></th>
              </tr>
            </thead>
            <tbody>
              <!-- Repeat row structure for each day -->
              <tr>
                <td>Monday</td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="monday-morning" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="monday-afternoon" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="monday-evening" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="monday-night" />
                  </label>
                </td>
              </tr>
              <tr>
                <td>Tuesday</td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="tuesday-morning" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="tuesday-afternoon" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="tuesday-evening" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="tuesday-night" />
                  </label>
                </td>
              </tr>
              <tr>
              </tr>
              <tr>
                <td>Wednesday</td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="wednesday-morning" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="wednesday-afternoon" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="wednesday-evening" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="wednesday-night" />
                  </label>
                </td>
              </tr>
              <tr>
                <td>Thursday</td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="thursday-morning" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="thursday-afternoon" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="thursday-evening" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="thursday-night" />
                  </label>
                </td>
              </tr>
              <tr>
                <td>Friday</td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="friday-morning" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="friday-afternoon" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="friday-evening" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="friday-night" />
                  </label>
                </td>
              </tr>
              <tr>
                <td>Saturday</td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="saturday-morning" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="saturday-afternoon" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="saturday-evening" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="saturday-night" />
                  </label>
                </td>
              </tr>
              <tr>
                <td>Sunday</td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="sunday-morning" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="sunday-afternoon" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="sunday-evening" />
                  </label>
                </td>
                <td>
                  <label class="checkbox-wrapper">
                    <input type="checkbox" name="sunday-night" />
                  </label>
                </td>
              </tr>
            </tbody>
          </table>

          <br />

          <div class="navigation-buttons">
            <a href="CreateStudentProfile_StudentTeamwork.php" class="btn-login">Back</a>
            <button type="submit" class="btn-login">Finish</button>
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
