<?php
// ai/recommendation_engine.php

/**
 * Generates health recommendations based on patient's latest health metrics.
 * This is a rule-based AI engine.
 *
 * @param array $patientData Associative array of patient's personal data (e.g., gender, date_of_birth).
 * @param array $latestMetrics Associative array of patient's latest health metrics (weight_kg, bmi, systolic_bp, etc.).
 * @return array An array of strings, each representing a recommendation.
 */
function getAIRecommendations(array $patientData, array $latestMetrics): array {
    $recommendations = [];

    // --- BMI-based Recommendations ---
    $bmi = $latestMetrics['bmi'] ?? null;
    if ($bmi !== null) {
        if ($bmi < 18.5) {
            $recommendations[] = "BMI indicates underweight. Recommend consulting a nutritionist for a calorie-dense, nutrient-rich diet plan and strength training exercises.";
        } else if ($bmi >= 25 && $bmi < 29.9) {
            $recommendations[] = "BMI indicates overweight. Advise a balanced diet with portion control and regular moderate-intensity physical activity (e.g., brisk walking 30 mins/day).";
        } else if ($bmi >= 30) {
            $recommendations[] = "BMI indicates obesity. Strongly recommend a comprehensive weight management program, including dietary changes, increased physical activity, and medical consultation.";
        }
    }

    // --- Blood Pressure-based Recommendations ---
    $systolic = $latestMetrics['systolic_bp'] ?? null;
    $diastolic = $latestMetrics['diastolic_bp'] ?? null;

    if ($systolic !== null && $diastolic !== null) {
        if ($systolic >= 140 || $diastolic >= 90) {
            $recommendations[] = "High Blood Pressure detected. Recommend reducing sodium intake, increasing potassium-rich foods, regular exercise, and stress management techniques. Advise consulting a doctor.";
        } else if (($systolic >= 120 && $systolic <= 129) && $diastolic < 80) {
            $recommendations[] = "Elevated Blood Pressure. Suggest adopting a DASH-style diet, limiting alcohol, and maintaining a healthy weight.";
        } else if ($systolic <= 90 || $diastolic <= 60) {
            $recommendations[] = "Low Blood Pressure detected. Advise adequate hydration, small frequent meals, avoiding prolonged standing, and consulting a doctor if symptomatic.";
        }
    }

    // --- Blood Sugar-based Recommendations ---
    $bloodSugar = $latestMetrics['blood_sugar_level_mg_dL'] ?? null;
    $fastingStatus = $latestMetrics['blood_sugar_fasting_status'] ?? null;

    if ($bloodSugar !== null) {
        if ($fastingStatus === 'Fasting (8+ hours)') {
            if ($bloodSugar > 125) {
                $recommendations[] = "High Fasting Blood Sugar detected. Recommend immediate dietary review focusing on low glycemic index foods, increased fiber, and regular physical activity. Advise follow-up with an endocrinologist.";
            } else if ($bloodSugar > 100 && $bloodSugar <= 125) {
                $recommendations[] = "Impaired Fasting Glucose (Pre-diabetes). Suggest lifestyle changes including diet modification (reduced sugar/refined carbs) and increased exercise to prevent progression to diabetes.";
            } else if ($bloodSugar < 70) {
                $recommendations[] = "Low Fasting Blood Sugar (Hypoglycemia). Advise carrying a quick source of glucose, eating regular meals, and reviewing medication/meal timing with a doctor.";
            }
        } else { // Non-Fasting or Random
            if ($bloodSugar >= 200) {
                $recommendations[] = "High Random Blood Sugar detected. Suggest dietary adjustments to reduce sugar and refined carbohydrates. Advise further testing for diabetes.";
            } else if ($bloodSugar >= 140 && $bloodSugar <= 199) {
                $recommendations[] = "Impaired Glucose Tolerance (Pre-diabetes). Recommend focusing on complex carbohydrates, lean proteins, and regular physical activity.";
            }
        }
    }

    // --- Age/Gender specific recommendations (examples, can be expanded) ---
    $age = calculateAgeFromDob($patientData['date_of_birth'] ?? null); // Requires calculateAgeFromDob from functions.php
    $gender = $patientData['gender'] ?? null;

    if ($age !== null) {
        if ($age >= 60) {
            $recommendations[] = "For seniors, focus on nutrient-dense foods, adequate protein for muscle maintenance, and bone health (Calcium, Vitamin D).";
        } else if ($age < 18) {
            $recommendations[] = "For younger patients, emphasize balanced nutrition for growth and development, and encourage active play/sports.";
        }
    }

    if ($gender === 'Female' && $age !== null && $age > 45) {
        $recommendations[] = "For women over 45, consider recommendations for bone density (calcium, vitamin D) and heart health.";
    }


    // Add more complex rules here as needed.
    // E.g., based on combination of factors, or health conditions from patientData.

    // Remove duplicate recommendations
    return array_unique($recommendations);
}
?>
