document.addEventListener('DOMContentLoaded', function() {
   
    document.getElementById('current-year').textContent = new Date().getFullYear();

    
    const celularInput = document.getElementById('celular');
    if (celularInput) {
        celularInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            
            e.target.value = value
                .replace(/^(\d{2})(\d)/g, '($1) $2')
                .replace(/(\d)(\d{4})$/, '$1-$2');
        });
    }

    
    const portfolioInput = document.getElementById('portfolio');
    if (portfolioInput) {
        // Atualiza contador de arquivos
        portfolioInput.addEventListener('change', function(e) {
            const files = e.target.files;
            const fileCount = document.getElementById('file-count');
            fileCount.textContent = files.length > 0 
                ? `${files.length} arquivo(s) selecionado(s)` 
                : 'Nenhum arquivo selecionado';

            updateFilePreview(files);
        });

        
        function updateFilePreview(files) {
            const maxFiles = parseInt(portfolioInput.getAttribute('data-max-files')) || 5;
            const previewContainer = document.getElementById('preview-container');
            const avisoLimite = document.getElementById('aviso-limite');

            if (files.length > maxFiles) {
                avisoLimite.textContent = `Máximo de ${maxFiles} arquivos permitidos!`;
                avisoLimite.style.display = 'block';
                portfolioInput.value = '';
                fileCount.textContent = 'Nenhum arquivo selecionado';
                return;
            } else {
                avisoLimite.style.display = 'none';
            }

            previewContainer.innerHTML = '';

            Array.from(files).forEach(file => {
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item';
                
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewItem.innerHTML = `
                            <img src="${e.target.result}" alt="Preview">
                            <button class="remove-btn" data-name="${file.name}">×</button>
                        `;
                        previewContainer.appendChild(previewItem);
                    };
                    reader.readAsDataURL(file);
                } else if (file.type.startsWith('audio/')) {
                    previewItem.innerHTML = `
                        <i class="fas fa-music"></i>
                        <p>${file.name.substring(0, 10)}...</p>
                        <button class="remove-btn" data-name="${file.name}">×</button>
                    `;
                    previewContainer.appendChild(previewItem);
                } else if (file.type.startsWith('video/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewItem.innerHTML = `
                            <video>
                                <source src="${e.target.result}" type="${file.type}">
                            </video>
                            <button class="remove-btn" data-name="${file.name}">×</button>
                        `;
                        previewContainer.appendChild(previewItem);
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewItem.innerHTML = `
                        <i class="fas fa-file"></i>
                        <p>${file.name.substring(0, 10)}...</p>
                        <button class="remove-btn" data-name="${file.name}">×</button>
                    `;
                    previewContainer.appendChild(previewItem);
                }
            });

            
            previewContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-btn')) {
                    const fileName = e.target.getAttribute('data-name');
                    e.target.parentElement.remove();
                    
                    const dataTransfer = new DataTransfer();
                    
                    Array.from(portfolioInput.files).forEach(file => {
                        if (file.name !== fileName) {
                            dataTransfer.items.add(file);
                        }
                    });
                    
                    portfolioInput.files = dataTransfer.files;
                    fileCount.textContent = dataTransfer.files.length > 0 
                        ? `${dataTransfer.files.length} arquivo(s) selecionado(s)` 
                        : 'Nenhum arquivo selecionado';
                }
            });
        }
    }

    
    const formArtista = document.getElementById('form-artista');
    if (formArtista) {
        formArtista.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validação do celular
            const celular = document.getElementById('celular').value.replace(/\D/g, '');
            if (celular && celular.length !== 11) {
                alert('Celular deve ter 11 dígitos (incluindo DDD)');
                return;
            }

            
            const submitBtn = formArtista.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            submitBtn.disabled = true;

            
            const formData = new FormData(formArtista);
            
            fetch('salvar_artista.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na rede');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Artista cadastrado com sucesso!');
                    formArtista.reset();
                    document.getElementById('preview-container').innerHTML = '';
                    document.getElementById('file-count').textContent = 'Nenhum arquivo selecionado';
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao enviar dados. Por favor, tente novamente.');
            })
            .finally(() => {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            });
        });
    }
});
