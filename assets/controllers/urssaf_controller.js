import { Controller } from '@hotwired/stimulus';
import axios from 'axios';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['employeeContribution', 'employerCost', 'endOfContractIndemnity', 'netSalary', 'netSalaryHeader', 'salaryRow', 'endOfContractIndemnityHeader', 'totalCddSalaryContainer', 'grossSalaryContainer'];

    updateUI(data) {
        this.employeeContributionTarget.textContent = data.employee_contribution;
        this.employerCostTarget.textContent = data.employer_cost;
        this.endOfContractIndemnityTarget.textContent = data.end_of_contract_indemnity;
        this.netSalaryTarget.textContent = data.gross_salary;
    }

    handleFormSubmission(event) {
        event.preventDefault();
        const formData = new FormData(this.element.querySelector('#urssafForm'));
        
        axios.post('/api/urssaf/calculate', formData)
            .then(response => {
                console.log(response.data);
            this.updateUI(response.data);
        })
        .catch(error => console.error('Error:', error));
    }

    connect() {
        console.log('urssaf_controller connected');
        this.updateDisplayOnLoad();
        this.element.addEventListener('submit', this.handleFormSubmission.bind(this));
    }

    updateDisplayBasedOnType(contractType) {
        const showEndOfContract = contractType === 'fixed_term';
        this.endOfContractIndemnityTarget.style.display = showEndOfContract ? 'table-cell' : 'none';
        this.endOfContractIndemnityHeaderTarget.style.display = showEndOfContract ? 'table-cell' : 'none';
        this.totalCddSalaryContainerTarget.style.display = showEndOfContract ? 'block' : 'none';

        if (showEndOfContract) {
            this.grossSalaryContainerTarget.classList.remove('col-md-12');
            this.grossSalaryContainerTarget.classList.add('col-md-6');
        } else {
            this.grossSalaryContainerTarget.classList.remove('col-md-6');
            this.grossSalaryContainerTarget.classList.add('col-md-12');
        }

        this.netSalaryHeaderTarget.textContent = contractType === 'internship' ? 'Gratification minimale' : 'Salaire net';
        this.resetValues();
    }

    resetValues() {
        this.employeeContributionTarget.textContent = '0';
        this.employerCostTarget.textContent = '0';
        this.endOfContractIndemnityTarget.textContent = '0';
        this.netSalaryTarget.textContent = '0';
    }

    updateDisplayOnLoad() {
        const defaultContractTypeInput = this.element.querySelector('input[name="salaire_input[contractType]"]:checked');
        if (defaultContractTypeInput) {
            this.updateDisplayBasedOnType(defaultContractTypeInput.value);
        }
    }

    updateDisplay(event) {
        const selectedContractType = event.target.value;
        this.updateDisplayBasedOnType(selectedContractType);
    }


}
