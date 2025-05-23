<?php 
require_once 'core/dbConfig.php'; 
require_once 'core/models.php'; 

if (!isset($_SESSION['username'])) {
  header("Location: login.php");
}

if ($_SESSION['is_client'] == 0) {
  header("Location: ../freelancer/index.php");
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <style>
      body { font-family: "Arial"; }
    </style>
  </head>
  <body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container-fluid">
      <div class="display-4 text-center">Gig Proposals. Double click to add interview</div>
      <div class="row justify-content-center">
        <?php $getGigById = getGigById($pdo, $_GET['gig_id']); ?>
        <div class="col-md-5">
          <div class="card shadow mt-4 p-4">
            <div class="card-header"><h4><?php echo $getGigById['gig_title']; ?> </h4></div>
            <div class="card-body">
              <p><?php echo $getGigById['gig_description']; ?></p>
              <p><i><?php echo $getGigById['date_added']; ?></i></p>
              <p><i><?php echo $_SESSION['username']; ?></i></p>
            </div>
          </div>
        </div>
        <div class="col-md-7">
          <div class="card shadow mt-4 p-4">
            <div class="card-header"><h4>Interviews</h4></div>
            <div class="card-body">
              <table class="table">
                <thead>
                  <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Time Start</th>
                    <th>Time End</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $getAllInterviewsByGig = getAllInterviewsByGig($pdo, $_GET['gig_id']); ?>
                  <?php foreach ($getAllInterviewsByGig as $row) { ?>
                  <tr>
                    <td><?php echo $row['first_name']; ?></td>
                    <td><?php echo $row['last_name']; ?></td>
                    <td><?php echo $row['time_start']; ?></td>
                    <td><?php echo $row['time_end']; ?></td>
                    <td>
                      <?php 
                        if ($row['status'] == "Accepted") {
                          echo "<span class='text-success'>Accepted</span>";
                        }
                        if ($row['status'] == "Rejected") {
                          echo "<span class='text-danger'>Rejected</span>";
                        } 
                        if ($row['status'] == "Pending") {
                          echo "Pending";
                        }
                      ?>  
                    </td>
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row justify-content-center p-4">
      <?php $getProposalsByGigId = getProposalsByGigId($pdo, $_GET['gig_id']); ?>
      <?php foreach ($getProposalsByGigId as $row) { ?>
      <div class="col-md-4 mt-4">
        <div class="card shadow gigProposalContainer p-4">
          <div class="card-body">
            <h2><?php echo $row['last_name'] . ", " . $row['first_name']; ?></h2>
            <p><?php echo $row['description']; ?></p>
            <p><i><?php echo $row['date_added']; ?></i></p>
            <form class="addNewInterviewForm d-none">
              <div class="form-group">
                <label for="time_start">Time Start</label>
                <input type="hidden" class="freelancer_id" value="<?php echo $row['user_id']; ?>">
                <input type="hidden" class="gig_id" value="<?php echo $_GET['gig_id']; ?>">
                <input type="datetime-local" class="time_start form-control">
              </div>
              <div class="form-group">
                <label for="time_end">Time End</label>
                <input type="datetime-local" class="time_end form-control">
                <input type="submit" class="btn btn-primary float-right mt-4">
              </div>
              <div class="error-message text-danger mt-2"></div>
            </form>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>

    <script>
      $('.gigProposalContainer').on('dblclick', function () {
        var addNewInterviewForm = $(this).find('.addNewInterviewForm');
        addNewInterviewForm.toggleClass('d-none');
      });

      $('.addNewInterviewForm').on('submit', function (event) {
        event.preventDefault();
        var form = $(this);
        var errorMessage = form.find('.error-message');
        errorMessage.text(""); // clear previous

        var formData = {
          freelancer_id: form.find('.freelancer_id').val(),
          gig_id: form.find('.gig_id').val(),
          time_start: form.find('.time_start').val(),
          time_end: form.find('.time_end').val(),
          insertNewGigInterview: 1
        };

        if (!formData.freelancer_id || !formData.gig_id || !formData.time_start || !formData.time_end) {
          errorMessage.text("Make sure no input fields are empty!");
          return;
        }

        $.ajax({
          type: "POST",
          url: "core/handleForms.php",
          data: formData,
          success: function (data) {
            if (data === 'success') {
              location.reload();
            } else if (data === 'duplicate') {
              errorMessage.text("This freelancer already has an interview for this gig.");
            } else if (data === 'conflict') {
              errorMessage.text("This time slot conflicts with another interview.");
            } else if (data === 'past_date') {
              errorMessage.text("Interview time cannot be in the past.");
            } else if (data === 'invalid_range') {
              errorMessage.text("Start time must be before end time.");
            } else {
              errorMessage.text("Unexpected error occurred.");
            }
          },
          error: function () {
            errorMessage.text("Server error. Please try again.");
          }
        });
      });
    </script>

    <?php include 'includes/footer.php'; ?>
  </body>
</html>