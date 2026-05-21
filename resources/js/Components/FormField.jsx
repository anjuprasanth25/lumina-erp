import React from "react";

export default function FormField({label, error, children}){
    return(
        <div style={{ marginBottom: '15px', width: '100%'}}>
            <label style={{ display: 'block', fontWeight: 'bold', marginBottom: '5px' }}>{ label }</label>
            { children }
            {error && (
                <span style={{ color: 'red', fontSize: '13px', marginTop: '4px', display: 'block' }}>{ error }</span>
            )}
        </div>
    );
}
