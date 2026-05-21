import React from 'react';
import { useForm, Head } from '@inertiajs/react';
import FormField from '../../Components/FormField';
import { route } from 'ziggy-js';


export default function Create({leaveTypes, countries}){

    const { data, setData, post, processing, errors } = useForm({
        leave_type_id: '',
        start_date: '',
        end_date: '',
        reason: '',
        destination_country_id: '',
        alternative_contact_no: ''
    });

    //handle form submission
    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('leave-requests.store'));
    };

    return(
        <div style={{ padding: '20px', fontFamily: 'sans-serif', maxWidth: '600px' }}>
            <Head title="Apply Leave" />
            <h2 style={{ fontWeight:'bold' }}>Submit a Leave Request</h2>
            <hr />
            <br />

            {/* Form markup goes here */}
            <form onSubmit={handleSubmit}>
                <FormField label="Leave Type *" error={errors.leave_type_id}>
                    <select style={formStyles.input}
                        value={data.leave_type_id} onChange={ e=> setData('leave_type_id', e.target.value)}>
                        <option value="">Select Leave Type</option>
                        {leaveTypes.map((type) => (
                            <option key={type.id} value={type.id}>{type.name}</option>
                        ))}
                    </select>
                </FormField>

                <FormField label="Destination Country(if travelling)" error={errors.destination_country_id}>
                    <select style={formStyles.input} value={data.destination_country_id}
                        onChange={ e => setData('destination_country_id', e.target.value)}>
                            <option value="">Select Country</option>
                            {countries.map((country) => (
                                <option key={country.id} value={country.id}>{country.name}</option>
                            ))}

                    </select>
                </FormField>

                <div style={{ display: 'flex', gap: '15px' }}>
                    <div style={{ flex:1 }}>
                        <FormField label="Start Date*" error={errors.start_date}>
                            <input style={formStyles.input} type="date" value={data.start_date}
                                onChange={e=>setData('start_date', e.target.value)}/>
                        </FormField>
                    </div>
                    <div style={{ flex:1 }}>
                        <FormField label="End Date*" error={errors.end_date}>
                            <input type="date" style={formStyles.input}
                                value={data.end_date} onChange={e=>setData('end_date',e.target.value)} />
                        </FormField>
                    </div>
                </div>

                <FormField label="Alternative Contact No" error={errors.alternative_contact_no}>
                        <input style={formStyles.input} type="text" value={data.alternative_contact_no}
                            onChange={e=>setData('alternative_contact_no', e.target.value)}
                            placeholder="e.g. +971 50 000 0000" />
                </FormField>

                <FormField label="Reason" error={errors.reason}>
                    <textarea style={formStyles.textarea} value={data.reason} onChange={e=>setData('reason', e.target.value)} rows="4"/>
                </FormField>

                <button type="submit"
                    disabled={processing}
                    style={formStyles.button(processing)}>
                    {processing ? 'Submitting...' : 'Submit Leave Request'}
                </button>

            </form>
        </div>
    );
}


const formStyles = {
    label: { display: 'block', fontWeight: 'bold', marginBottom: '5px' },
    input: { width: '100%', padding: '8px', boxSizing: 'border-box' },
    textarea: {
        width: '100%',
        padding: '8px',
        boxSizing: 'border-box',
        marginTop: '5px',
        borderRadius: '4px',
        border: '1px solid #ccc',
        fontFamily: 'sans-serif'
    },
    button: (isProcessing) => ({
        padding: '10px 20px',
        backgroundColor: isProcessing ? '#ccc' : '#007bff',
        color: '#fff',
        border: 'none',
        borderRadius: '4px',
        fontWeight: 'bold',
        cursor: isProcessing ? 'not-allowed' : 'pointer'
    })
};
