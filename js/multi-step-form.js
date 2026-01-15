document.addEventListener("DOMContentLoaded", () => {
  const nextButtons = document.querySelectorAll(".btn-next");
  const prevButtons = document.querySelectorAll(".btn-prev");
  const formSteps = document.querySelectorAll(".form-step");
  const progress = document.getElementById("progress");
  const progressSteps = document.querySelectorAll(".progress-step");
  const stepLabel = document.getElementById("stepLabel");
  let currentStep = 0;

  nextButtons.forEach(button => {
      button.addEventListener("click", () => {
          if (validateCurrentStep()) {
              if (currentStep < formSteps.length - 1) {
                  formSteps[currentStep].classList.remove("form-step-active", "slide-in-left", "slide-in-right");
                  formSteps[currentStep].classList.add("slide-out-left");
                  currentStep++;
                  formSteps[currentStep].classList.add("form-step-active", "slide-in-right");
                  updateProgressbar();
              }
          }
      });
  });

  prevButtons.forEach(button => {
      button.addEventListener("click", () => {
          if (currentStep > 0) {
              formSteps[currentStep].classList.remove("form-step-active", "slide-in-left", "slide-in-right");
              formSteps[currentStep].classList.add("slide-out-right");
              currentStep--;
              formSteps[currentStep].classList.add("form-step-active", "slide-in-left");
              updateProgressbar();
          }
      });
  });

  function validateCurrentStep() {
      const currentFormStep = formSteps[currentStep];
      const inputs = currentFormStep.querySelectorAll("input, select, textarea");
      let valid = true;

      inputs.forEach(input => {
          if (!input.checkValidity()) {
              valid = false;
              input.classList.add("is-invalid");
          } else {
              input.classList.remove("is-invalid");
          }
      });

      if (!valid) {
          currentFormStep.classList.add("was-validated");
      } else {
          currentFormStep.classList.remove("was-validated");
      }

      return valid;
  }

  function updateProgressbar() {
      if (!progress || progressSteps.length === 0) {
          return;
      }

      progressSteps.forEach((progressStep, idx) => {
          if (idx < currentStep + 1) {
              progressStep.classList.add("progress-step-active");
          } else {
              progressStep.classList.remove("progress-step-active");
          }
      });

      const progressActive = document.querySelectorAll(".progress-step-active");
      progress.style.width = ((progressActive.length - 1) / (progressSteps.length - 1)) * 100 + "%";

      if (stepLabel) {
        const currentTitle = progressSteps[currentStep]?.dataset?.title || "";
        stepLabel.textContent = `Step ${currentStep + 1} of ${progressSteps.length}: ${currentTitle}`;
      }
  }

  updateProgressbar();
});
