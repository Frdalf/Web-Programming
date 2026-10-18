const mobileMenu = document.getElementById('mobile-menu');
const menuBtn = document.getElementById('menu-btn');
const iconOpen = document.getElementById('icon-open');
const iconClose = document.getElementById('icon-close');

menuBtn.addEventListener('click', () => {
  const isHidden = mobileMenu.classList.toggle('hidden');

  iconOpen.classList.toggle('hidden', !isHidden);
  iconClose.classList.toggle('hidden', isHidden);

  menuBtn.setAttribute('aria-expanded', String(!isHidden));
});

mobileMenu.querySelectorAll('a').forEach((link) => {
  link.addEventListener('click', () => {
    mobileMenu.classList.add('hidden');
    iconOpen.classList.remove('hidden');
    iconClose.classList.add('hidden');
    menuBtn.setAttribute('aria-expanded', 'false');
  });
});


const form = document.getElementById('contact-form');
const successMsg = document.getElementById('form-success');

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const rules = {
  name: (value) => {
    if (value === '') return 'Nama wajib diisi.';
    if (value.length < 3) return 'Nama minimal 3 karakter.';
    return '';
  },
  email: (value) => {
    if (value === '') return 'Email wajib diisi.';
    if (!EMAIL_PATTERN.test(value)) return 'Format email tidak valid (contoh: nama@email.com).';
    return '';
  },
  message: (value) => {
    if (value === '') return 'Pesan wajib diisi.';
    if (value.length < 10) return `Pesan minimal 10 karakter (sekarang ${value.length}).`;
    return '';
  },
};

function validateField(field) {
  const value = field.value.trim();
  const errorMessage = rules[field.name](value);
  const errorEl = document.getElementById(`${field.name}-error`);

  errorEl.textContent = errorMessage;

  field.classList.toggle('border-red-500', errorMessage !== '');
  field.classList.toggle('border-gray-300', errorMessage === '');

  return errorMessage === '';
}

const fields = [
  document.getElementById('name'),
  document.getElementById('email'),
  document.getElementById('message'),
];

fields.forEach((field) => {
  field.addEventListener('input', () => {
    validateField(field);
    successMsg.classList.add('hidden');
  });
});

form.addEventListener('submit', (event) => {
  event.preventDefault();

  successMsg.classList.add('hidden');

  const validationResults = fields.map(validateField);
  const allValid = validationResults.every((isValid) => isValid);

  if (!allValid) return;

  const userName = document.getElementById('name').value.trim();

  successMsg.textContent = `Terima kasih, ${userName}! Pesanmu sudah berhasil dikirim.`;
  successMsg.classList.remove('hidden');

  form.reset();

  fields.forEach((field) => {
    field.classList.remove('border-red-500');
    field.classList.add('border-gray-300');
  });
});
