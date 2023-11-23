import { Controller } from '@hotwired/stimulus';
import axios from 'axios';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['employeeContribution', 'employerCost', 'endOfContractIndemnity', 'netSalary', 'salaryRow', 'endOfContractIndemnityHeader'];

    connect() {
        this.updateDisplayOnLoad();
    }

    updateDisplayOnLoad() {
        const defaultContractTypeInput = this.element.querySelector('input[name="salaire_input[contractType]"]:checked');
        if (defaultContractTypeInput) {
            this.updateDisplayBasedOnType(defaultContractTypeInput.value);
        }
    }

    updateDisplay() {
        const selectedContractType = this.element.querySelector('input[name="salaire_input[contractType]"]:checked').value;
        this.updateDisplayBasedOnType(selectedContractType);
    }

    updateDisplayBasedOnType(contractType) {
        const showEndOfContract = contractType === 'fixed_term';
        this.endOfContractIndemnityTarget.style.display = showEndOfContract ? 'table-cell' : 'none';
        this.endOfContractIndemnityHeaderTarget.style.display = showEndOfContract ? 'table-cell' : 'none';

        this.netSalaryTarget.textContent = contractType === 'internship' ? 'Gratification minimale' : 'Salaire net';
    }

    fetchData() {
        const grossSalaryInput = this.element.querySelector('input[name="salaire_input[grossSalary]"]');

        if (grossSalaryInput) {
            const grossSalary = grossSalaryInput.value;

            axios.post('/api/urssaf/calculate', {
                grossSalary: parseFloat(grossSalary),
                contractType: selectedContractType
            }).then(response => {
                this.updateUI(response.data);
            }).catch(error => console.error('Error:', error));
        }
    }

    updateUI(data) {
        if (data.error) {
            console.error(data.error);
            return;
        }

        this.employeeContributionTarget.textContent = data.employee_contribution;
        this.employerCostTarget.textContent = data.employer_cost;
        this.endOfContractIndemnityTarget.textContent = data.end_of_contract_indemnity;
        this.netSalaryTarget.textContent = data.net_salary;
    }

    handleFormSubmission(event) {
        event.preventDefault();
        const selectedContractType = this.element.querySelector('input[name="salaire_input[contractType]"]:checked').value;
        this.fetchData(selectedContractType);
    }


}
