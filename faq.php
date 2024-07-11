<?php
// File: faq.php
$page_title = "FAQ - Why Join Africhama?";
include './includes/header.php';
?>

<style>
    .faq-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    .faq-item {
        background-color: #f9f9f9;
        border-radius: 8px;
        margin-bottom: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .faq-question {
        font-size: 18px;
        font-weight: bold;
        padding: 20px;
        background-color: #2ecc71;
        color: white;
        cursor: pointer;
    }
    .faq-answer {
        padding: 0 20px;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease, padding 0.3s ease;
    }
    .faq-item.active .faq-answer {
        padding: 20px;
        max-height: 1000px;
    }
    .faq-item:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
</style>

<div class="container mt-5 faq-container">
    <h1 class="text-center mb-5">Why Join Africhama?</h1>
    
    <div class="faq-list">
        <?php
        $faqs = [
            "Flexibility" => "Africhama business provides the freedom to work at your own pace and according to your schedule. It allows you to choose when and where you work, providing a great work-life balance.",
            "Low start-up costs" => "Starting Africhama business requires minimal investment compared to traditional brick-and-mortar establishments. This reduces financial risks and barriers to entry.",
            "Global reach" => "The internet has made the world a smaller place. Participating in Africhama networking business grants access to a global marketplace and allows you to connect with potential customers, partners, and suppliers from around the world.",
            "Unlimited earning potential" => "With Africhama networking business, your income potential is not limited by a fixed salary or hourly wage. Instead, it is determined by your efforts, skills, and ability to build a successful network.",
            "Personal development" => "Engaging in Africhama networking business offers an opportunity for personal growth and skill development, including communication, leadership, and salesmanship.",
            "Work from home" => "Africhama networking business provides the luxury of working from the comfort of your own home. This eliminates commuting time and expenses, improving productivity and reducing stress.",
            "Diverse income streams" => "Africhama networking business often offer multiple ways to earn income, including retail sales, team building, incentives, and bonuses. This diversification can provide stability and enhance earning potential.",
            "Continual learning" => "Africhama networking business foster an environment of continuous learning. You can acquire new skills, stay informed about market trends, and participate in training and development programs offered by Africhama network.",
            "Passive income" => "In Africhama, as your network grows, you earn passive income from the efforts of your team members. This residual income provides financial security and long-term wealth generation.",
            "Personal fulfillment" => "Africhama business allows you to align your work with your passions and interests. Pursuing something you genuinely enjoy leads to a sense of fulfillment and satisfaction in your everyday life.",
            // ... Add all other FAQs here
        ];

        foreach ($faqs as $question => $answer) {
            echo "<div class='faq-item'>
                    <div class='faq-question'>$question</div>
                    <div class='faq-answer'>$answer</div>
                  </div>";
        }
        ?>
    </div>
</div>

<script>
document.querySelectorAll('.faq-question').forEach(question => {
    question.addEventListener('click', () => {
        const item = question.parentNode;
        item.classList.toggle('active');
    });
});
</script>

<?php include './includes/footer.php'; ?>