<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class UserModel extends CoreModel
{
    protected $db;
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function processLogin($email, $password)
    {
        $stmt = $this->db->prepare("SELECT staff_id, firstname, lastname, email, role, password FROM staffs WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (!password_verify($password, $user['password'])) {
                return ['status' => false, 'message' => 'Incorrect email or password'];
            }
            unset($user['password']);
            return [
                'status' => true,
                'message' => 'Login successful',
                'user' => $user,
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Incorrect email or password'
            ];
        }
    }

    public function forgot_password($email)
    {
        $stmt = $this->db->prepare("SELECT staff_id, firstname FROM staffs WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['status' => false, 'message' => 'No account found with this email address'];
        }

        $staff = $result->fetch_assoc();

        // Generate 6-digit temporary password
        $plain_password = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hashed = password_hash($plain_password, PASSWORD_DEFAULT);

        // Save hashed password to DB
        $stmt2 = $this->db->prepare("UPDATE staffs SET password = ? WHERE staff_id = ?");
        $stmt2->bind_param("si", $hashed, $staff['staff_id']);
        if (!$stmt2->execute()) {
            return ['status' => false, 'message' => 'Failed to reset password. Please try again.'];
        }

        // Send email with plain password
        $mailResult = $this->sendForgotPasswordEmail($email, $staff['firstname'], $plain_password);
        if (!$mailResult['status']) {
            return ['status' => false, 'message' => 'Password reset but email delivery failed. Contact your administrator.'];
        }

        return ['status' => true, 'message' => 'A temporary password has been sent to your email address.'];
    }

    public function change_password($staff_id, $current_password, $new_password)
    {
        $stmt = $this->db->prepare("SELECT password FROM staffs WHERE staff_id = ?");
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['status' => false, 'message' => 'Account not found'];
        }

        $row = $result->fetch_assoc();

        if (!password_verify($current_password, $row['password'])) {
            return ['status' => false, 'message' => 'Current password is incorrect'];
        }

        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt2  = $this->db->prepare("UPDATE staffs SET password = ? WHERE staff_id = ?");
        $stmt2->bind_param("si", $hashed, $staff_id);

        if ($stmt2->execute()) {
            return ['status' => true, 'message' => 'Password changed successfully'];
        }

        return ['status' => false, 'message' => 'Failed to update password. Please try again.'];
    }

    private function sendForgotPasswordEmail($email, $name, $plain_password)
    {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USERNAME'];
            $mail->Password   = $_ENV['SMTP_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->Timeout    = 15;

            $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], 'TAMEC - Care Staffing Services');
            $mail->addAddress($email, $name);

            $mail->isHTML(true);
            $mail->Subject = 'Your Temporary Password - TAMEC';
            $mail->Body    = $this->forgotPasswordEmailTemplate($name, $plain_password);

            $mail->send();
            return ['status' => true];
        } catch (PHPMailerException $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    private function forgotPasswordEmailTemplate($name, $password)
    {
        return '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Password Reset - TAMEC</title>
</head>
<body style="margin:0;padding:0;background-color:#eef2f7;font-family:\'Helvetica Neue\',Arial,sans-serif;">

  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef2f7;padding:48px 16px;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;border-radius:16px;overflow:hidden;box-shadow:0 8px 32px rgba(0,51,102,0.12);">

          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg,#003366 0%,#004d99 100%);padding:36px 48px;text-align:center;">
              <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
                <tr>
                  <td style="text-align:center;">
                    <p style="margin:0;color:#99CC33;font-size:11px;font-weight:700;letter-spacing:4px;text-transform:uppercase;">CARE STAFFING SERVICES</p>
                    <h1 style="margin:6px 0 0;color:#ffffff;font-size:32px;font-weight:800;letter-spacing:2px;">TAMEC</h1>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Icon Banner -->
          <tr>
            <td style="background-color:#003366;padding:0 0 24px;text-align:center;">
              <div style="display:inline-block;background-color:#99CC33;border-radius:50%;width:64px;height:64px;line-height:64px;text-align:center;font-size:28px;">
                &#128273;
              </div>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="background-color:#ffffff;padding:40px 48px;">

              <h2 style="margin:0 0 8px;color:#003366;font-size:22px;font-weight:700;text-align:center;">Password Reset Request</h2>
              <p style="margin:0 0 28px;color:#6b7280;font-size:15px;line-height:1.7;text-align:center;">
                Hi <strong style="color:#003366;">' . htmlspecialchars($name) . '</strong>, we received a request to reset your TAMEC account password. Your new temporary password is below.
              </p>

              <!-- Password Box -->
              <table cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
                <tr>
                  <td style="background-color:#f0f9e0;border:2px dashed #99CC33;border-radius:12px;padding:28px;text-align:center;">
                    <p style="margin:0 0 10px;color:#6b7280;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:2px;">Your Temporary Password</p>
                    <p style="margin:0;font-size:42px;font-weight:900;color:#003366;letter-spacing:12px;font-family:\'Courier New\',monospace;">' . $password . '</p>
                  </td>
                </tr>
              </table>

              <!-- Warning Box -->
              <table cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
                <tr>
                  <td style="background-color:#fffbeb;border-left:4px solid #f59e0b;border-radius:0 8px 8px 0;padding:14px 18px;">
                    <p style="margin:0;color:#92400e;font-size:13px;line-height:1.7;">
                      <strong>Important:</strong> This is a temporary password. Please log in and go to <strong>Change Password</strong> to set a new secure password immediately.
                    </p>
                  </td>
                </tr>
              </table>

              <!-- Steps -->
              <table cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:32px;">
                <tr>
                  <td style="padding:0;">
                    <p style="margin:0 0 12px;color:#374151;font-size:14px;font-weight:600;">Next steps:</p>
                    <table cellpadding="0" cellspacing="0">
                      <tr>
                        <td style="padding:4px 0;vertical-align:top;">
                          <span style="display:inline-block;background-color:#99CC33;color:#fff;border-radius:50%;width:20px;height:20px;line-height:20px;text-align:center;font-size:11px;font-weight:700;margin-right:10px;">1</span>
                          <span style="color:#4b5563;font-size:14px;">Log in with the temporary password above</span>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding:8px 0 4px;vertical-align:top;">
                          <span style="display:inline-block;background-color:#99CC33;color:#fff;border-radius:50%;width:20px;height:20px;line-height:20px;text-align:center;font-size:11px;font-weight:700;margin-right:10px;">2</span>
                          <span style="color:#4b5563;font-size:14px;">Navigate to <strong>Change Password</strong> in the sidebar</span>
                        </td>
                      </tr>
                      <tr>
                        <td style="padding:8px 0 4px;vertical-align:top;">
                          <span style="display:inline-block;background-color:#99CC33;color:#fff;border-radius:50%;width:20px;height:20px;line-height:20px;text-align:center;font-size:11px;font-weight:700;margin-right:10px;">3</span>
                          <span style="color:#4b5563;font-size:14px;">Set a new strong password of your choice</span>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <p style="margin:0;color:#9ca3af;font-size:12px;text-align:center;line-height:1.6;">
                If you did not request this password reset, please contact your system administrator immediately.<br>
                Do not share this password with anyone.
              </p>

            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background:linear-gradient(135deg,#003366 0%,#004d99 100%);padding:24px 48px;text-align:center;">
              <p style="margin:0 0 4px;color:#99CC33;font-size:13px;font-weight:600;">TAMEC - Care Staffing Services</p>
              <p style="margin:0;color:#7a9fbf;font-size:11px;">This is an automated message. Please do not reply to this email.</p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>';
    }
}
