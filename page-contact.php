<?php get_header(); ?>

<div class="page-content">
  <h1>Contact Us</h1>
  <p>
    Have a question, suggestion, or want to report a problem with a game listing?
    We'd love to hear from you. Fill out the form below and our team will get back to you within 48 hours.
  </p>

  <form class="contact-form" id="contact-form" novalidate>
    <div class="form-group">
      <label for="cf-name">Your Name <span style="color:#e53935">*</span></label>
      <input type="text" id="cf-name" name="name" placeholder="e.g. Rahul Sharma" required>
    </div>
    <div class="form-group">
      <label for="cf-email">Email Address <span style="color:#e53935">*</span></label>
      <input type="email" id="cf-email" name="email" placeholder="you@example.com" required>
    </div>
    <div class="form-group">
      <label for="cf-subject">Subject <span style="color:#e53935">*</span></label>
      <select id="cf-subject" name="subject">
        <option value="general">General Enquiry</option>
        <option value="game-issue">Issue with a Game Listing</option>
        <option value="dmca">DMCA / Copyright</option>
        <option value="advertising">Advertising</option>
        <option value="feedback">Feedback / Suggestion</option>
        <option value="other">Other</option>
      </select>
    </div>
    <div class="form-group">
      <label for="cf-message">Message <span style="color:#e53935">*</span></label>
      <textarea id="cf-message" name="message" placeholder="Tell us how we can help you…" required></textarea>
    </div>
    <button type="submit" class="btn-submit">Send Message</button>
  </form>

  <hr style="margin:32px 0; border-color:#eee;">

  <h2>Other Ways to Reach Us</h2>
  <p><strong>Email:</strong> <a href="mailto:support@steppa.in" style="color:var(--green)">support@steppa.in</a></p>
  <p><strong>Response Time:</strong> Within 48 business hours (Mon–Sat)</p>
  <p>
    For DMCA or copyright removal requests, please use the DMCA subject in the form above and include
    the URL of the page in question, the copyrighted content, and your ownership proof.
  </p>
</div>

<?php get_footer(); ?>
