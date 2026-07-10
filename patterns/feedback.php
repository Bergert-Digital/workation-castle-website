<?php
/**
 * Title: Feedback
 * Slug: pediment-child/feedback
 * Categories: pediment-child
 * Description: Feedback page — a short form for guests and visitors to share feedback.
 * Inserter: no
 */
// phpcs:ignoreFile -- block pattern content (verbatim block markup).
?>
<!-- wp:pediment-child/page-hero {"align":"full","eyebrow":"Your stay","headline":"Feedback","lead":"Stayed with us, or just browsing? Tell us what worked and what didn't — it genuinely helps.","imageUrl":"https://workationcastle.com/wp-content/uploads/2022/12/Castello-towards-Lugano.jpg","imageAlt":"View from Castello Carlazzo over the valley towards Lugano"} /-->

<!-- wp:group {"className":"wc-wrap","layout":{"type":"constrained"},"style":{"spacing":{"blockGap":"var:preset|spacing|60"}}} -->
<div class="wp-block-group wc-wrap is-layout-constrained wp-block-group-is-layout-constrained">
<!-- wp:pediment/prose -->
<!-- wp:heading -->
<h2 class="wp-block-heading">Share your feedback</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>A sentence or two is plenty — praise, gripes, or ideas all welcome. See how we handle your details in our <a href="/privacy-policy/">Privacy Policy</a>.</p>
<!-- /wp:paragraph -->
<!-- /wp:pediment/prose -->

<!-- wp:group {"className":"wc-form-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group wc-form-card is-layout-constrained wp-block-group-is-layout-constrained">
<!-- wp:pediment/form {"successMessage":"Thanks for the feedback — we really appreciate it.","submitLabel":"Send"} -->
<!-- wp:pediment/form-field {"fieldType":"textarea","label":"Message","fieldName":"message","required":true} /-->
<!-- wp:pediment/form-field {"fieldType":"email","label":"Email address","fieldName":"email","helpText":"Only if you'd like a reply."} /-->
<!-- wp:pediment/form-field {"fieldType":"checkbox","label":"I agree that Workation Castle may process my feedback as described in the Privacy Policy.","fieldName":"consent","required":true} /-->
<!-- /wp:pediment/form -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
